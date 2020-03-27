<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 17/10/2019
 * Time: 14:45
 */

namespace Kookaburra\SystemAdmin\Command;

use App\Migrations\SqlLoadTrait;
use App\Util\GlobalHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\TableExistsException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Kookaburra\SystemAdmin\Entity\Action;
use Kookaburra\SystemAdmin\Entity\Module;
use Kookaburra\SystemAdmin\Entity\NotificationEvent;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\ORM\EntityManagerInterface;
use Kookaburra\SystemAdmin\Manager\UpgradeManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ModuleInstallCommand
 * @package Kookaburra\SystemAdmin\Command
 */
class ModuleInstallCommand extends Command
{
    use SqlLoadTrait;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array
     */
    private $version;

    /**
     * @var Module
     */
    private $module;

    /**
     * @var array
     */
    private $sqlContent = [];

    /**
     * @var array
     */
    private $connection;

    /**
     * @var string
     */
    protected static $defaultName = 'kookaburra:module:install';

    /**
     * @var UpgradeManager
     */
    private $manager;

    /**
     * ModuleInstallCommand constructor.
     * @param EntityManagerInterface $em
     * @param UpgradeManager $manager
     * @param GlobalHelper $helper
     */
    public function __construct(EntityManagerInterface $em, UpgradeManager $manager, GlobalHelper $helper)
    {
        parent::__construct();
        $this->em = $em;
        $this->manager = $manager;
        $this->connection = $em->getConnection();
    }

    protected function configure()
    {
        $this
            ->setDescription('Adds Symfony Bundles for Kookaburra to the Module/Action/Permission Tables and runs installation script in the module.')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command installs modules into the Module, Action and Permission tables as specified in the bundle <comment>version</comment> file.

Executes the <info>SQL</info> script files in the bundle Resources/migration folder if it exists. 

Installation, core and foreign-constraint sql files are handled as part of installation.  Any upgrade{version} sql file is handled as an upgrade.

  <info>php %command.full_name%</info>

EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->newLine();
        $io->text('Commencing Module Build');
        if (!$this->em instanceof EntityManager) {
            dd($this);
            $io->warning('The entity manager is not available!');
            return 1;
        }
        if (! $this->em->getConnection()->connect()) {
            dd($this);
            $io->warning('The database is not available. Check that the database settings are available and are valid!');
            return 1;
        }
        $kernel = $this->getApplication()->getKernel();

        $finder = new Finder();
        $projectDir = $kernel->getContainer()->getParameter('kernel.project_dir');
        $exitCode = 0;

        $io->text('Module Check:');
        $finder = new Finder();
        $bundles = $finder->directories()->in($projectDir . '/vendor/kookaburra/')->depth(0);

        foreach ($bundles as $bundle) {
            $this->version = null;
            $io->text('Checking bundle <info>' . $bundle->getBasename() . '</info>');
            // do the installation stuff

            if (!$this->isModuleInstalled($bundle)) {
                $io->text('Checking bundle <info>' . $bundle->getBasename() . '</info>');

                $io->newLine();

                // Do Migration stuff
                if (is_file($bundle->getRealPath() . '/src/Resources/config/version.yaml')) {
                    $version = Yaml::parse(file_get_contents($bundle->getRealPath() . '/src/Resources/config/version.yaml'));
                    $module = $this->em->getRepository(Module::class)->findOneByName($this->version['name']);

                    if (isset($version['module']))
                        $exitCode += $this->manager->writeModuleDetails($version['module']);
                    if (isset($version['events']))
                        $exitCode += $this->manager->writeEventDetails($version['events'],$version['name']);
                    if ($exitCode === 0)
                        $this->setModuleVersion($this->getModule(), $version['version']);

                    if (!$this->manager->hasModuleVersion($this->getModule(), 'installation')) {
                        if (is_file($bundle->getRealpath() . '/src/Resources/migration/installation.sql')) {
                            $io->text('Installation');
                            $this->setSqlContent([]);
                            $this->getSql($bundle->getRealpath() . '/src/Resources/migration/installation.sql');
                            if ($this->writeFileSql($input, $output) > 0)
                                return 1;
                            else
                                $this->setModuleVersion($this->getModule(), 'installation');
                        }
                    }

                    if (!$this->manager->hasModuleVersion($this->getModule(), 'core')) {
                        if (is_file($bundle->getRealpath() . '/src/Resources/migration/core.sql')) {
                            $io->text('Core');
                            $this->setSqlContent([]);
                            $this->getSql($bundle->getRealpath() . '/src/Resources/migration/core.sql');
                            if ($this->writeFileSql($input, $output) > 0)
                                return 1;
                            else
                                $this->setModuleVersion($this->getModule(), 'core');
                        }
                    }

                    if (!$this->manager->hasModuleVersion($this->getModule(), 'foreign-constraint')) {
                        {
                            if (is_file($bundle->getRealpath() . '/src/Resources/migration/foreign-constraint.sql')) {
                                $io->text('Foreign Constraint');
                                $this->setSqlContent([]);
                                $this->getSql($bundle->getRealpath() . '/src/Resources/migration/foreign-constraint.sql');
                                if ($this->writeFileSql($input, $output) > 0)
                                    return 1;
                                else
                                    $this->setModuleVersion($this->getModule(), 'foreign-constraint');
                            }
                        }
                    }
                }
            }
            // Add upgrades here ...

            $finder = new Finder();
            $updates = $finder->files()->in($bundle->getRealpath() . '/src/Resources/migration')->depth(0)->name('Version*.sql')->sortByName();
            if ($updates->hasResults()) {
                foreach($updates as $update) {
                    if (!$this->manager->hasModuleVersion($this->getModule(), str_replace(['version', 'Version', '.sql'], '', $update->getBasename()))) {
                        {
                            $io->text(sprintf('Update for <info>%s</info>.', $update->getBasename()));
                            $this->setSqlContent([]);
                            $this->getSql($update->getRealpath());
                            if ($this->writeFileSql($input, $output) > 0) {
                                $io->error(sprintf('Update for %s failed.', $update->getBasename()));
                                return 1;
                            }
                            else {
                                $this->manager->setModuleVersion($this->getModule(), str_replace(['version', 'Version', '.sql'], '', $update->getBasename()));
                                $io->success(sprintf('Update for %s completed', $update->getBasename()));
                            }
                        }
                    }

                }
            }

            $name = isset($this->version['name']) ? $this->version['name'] : ucfirst($bundle->getBasename());
            $io->success('Installation completed and database created for bundle ' . $name);

        }

        return $exitCode > 0 ? 1 : 0;
    }

    /**
     * isModuleInstalled
     * @param File $bundle
     * @return bool
     */
    private function isModuleInstalled(SplFileInfo $bundle): bool
    {
        if (!is_file($bundle->getRealPath(). '/src/Resources/config/version.yaml'))
            return true;

        $this->version = Yaml::parse(file_get_contents($bundle->getRealPath(). '/src/Resources/config/version.yaml'));
        if (!isset($this->version['name']))
            return true;

        $module = $this->em->getRepository(Module::class)->findOneByName($this->version['name']);

        if (null === $module)
            return false;

        $this->setModule($module);
        return $this->manager->hasModuleVersion($module, $this->version['version']);
    }

    /**
     * @return Module|null
     */
    public function getModule(): ?Module
    {
        return $this->module;
    }

    /**
     * Module.
     *
     * @param Module $module
     * @return ModuleInstallCommand
     */
    public function setModule(Module $module): ModuleInstallCommand
    {
        $this->module = $module;
        return $this;
    }

    private function addSql(string $line): ModuleInstallCommand
    {
        $this->getSqlContent();
        $this->sqlContent[] = $line;
        return $this;
    }

    /**
     * getSqlContent
     * @return array
     */
    public function getSqlContent(): array
    {
        return $this->sqlContent = $this->sqlContent ?: [];
    }

    /**
     * setSqlContent
     * @param array $sqlContent
     * @return ModuleInstallCommand
     */
    public function setSqlContent(array $sqlContent): ModuleInstallCommand
    {
        $this->sqlContent = $sqlContent;
        return $this;
    }

    /**
     * writeFileSql
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    private function writeFileSql(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->em->beginTransaction();
            foreach ($this->getSqlContent() as $sql) {
                $sql = str_replace('IF NOT EXISTS ', '', $sql);
                $sql = str_replace("CREATE TABLE ", "CREATE TABLE IF NOT EXISTS ", $sql);
                $sql = str_replace("__prefix__", $this->getPrefix(), $sql);
                if ('' !== trim($sql))
                    $this->em->getConnection()->exec($sql);
            }
            $this->em->commit();
            return 0;
        } catch (UniqueConstraintViolationException $e) {
            return 0;
        } catch (TableExistsException | UniqueConstraintViolationException | DBALException | PDOException $e) {
            $this->em->rollback();
            $io = new SymfonyStyle($input, $output);
            $io->newLine();
            $io->error($e->getMessage());
            return 1;
        }
    }

    /**
     * setModuleVersion
     * @param Module $module
     * @param string $version
     * @return $this
     */
    private function setModuleVersion(Module $module, string $version): self
    {
        $this->manager->setModuleVersion($module, $version);

        return $this;
    }
}