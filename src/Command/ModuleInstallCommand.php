<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 17/10/2019
 * Time: 14:45
 */

namespace Kookaburra\SystemAdmin\Command;

use Kookaburra\SystemAdmin\Entity\Action;
use Kookaburra\SystemAdmin\Entity\Module;
use Kookaburra\SystemAdmin\Entity\ModuleUpgrade;
use Kookaburra\SystemAdmin\Entity\Permission;
use Kookaburra\SystemAdmin\Entity\Role;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
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
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array
     */
    private $version;

    /**
     * @var string
     */
    protected static $defaultName = 'module:install';

    /**
     * ModuleInstallCommand constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Adds Symfony Bundles for Kookaburra to the Module/Action/Permission Tables and runs installation script in the module.')
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command installs modules into the Module, Action and Permission tables as specified in the bundle <comment>version</comment> file.

Executes the intsallation.sql script file in the bundle Resources/migration folder if it exists. 

  <info>php %command.full_name%</info>

EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->newLine();
        if (! $this->em->getConnection()->connect()) {
            $io->warning('The database is not available. Check that the database settings are available and are valid!');
            return 1;
        }
        $kernel = $this->getApplication()->getKernel();

        $finder = new Finder();

        $bundles = $finder->directories()->in($kernel->getContainer()->getParameter('kernel.project_dir') . '/vendor/kookaburra/')->depth(0);
        $exitCode = 0;
        foreach ($bundles as $bundle) {
            $this->version = null;
            // do the installation stuff
            if (!$this->isModuleInstalled($bundle)) {
                $io->text('Installing module <info>'.(isset($this->version['name']) ? $this->version['name'] : $bundle->getBasename()).'</info>.');

                $io->newLine();

                if (is_file($bundle->getRealpath() . '/src/Resources/migration/installation.sql')) {
                    $content = file($bundle->getRealpath() . '/src/Resources/migration/installation.sql');
                    try {
                        $this->em->beginTransaction();
                        foreach ($content as $sql) {
                            if ('' !== trim($sql))
                                $this->em->getConnection()->exec($sql);
                        }
                        $this->em->commit();
                    } catch (PDOException $e) {
                        $this->em->rollback();
                        $io = new SymfonyStyle($input, $output);
                        $io->newLine();
                        $io->error($e->getMessage());
                        return 1;
                    }
                }

                // Do Migration stuff
                if (is_file($bundle->getRealPath() . '/src/Resources/config/version.yaml')) {
                    $version = Yaml::parse(file_get_contents($bundle->getRealPath() . '/src/Resources/config/version.yaml'));
                    if (isset($version['module'])) {
                        $exitCode += $this->writeModuleDetails($version['module'], $input, $output, $io);
                    }
                }
            }
        }

        return $exitCode > 0 ? 1 : 0;
    }

    /**
     * writeModuleDetails
     * @param array $w
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    private function writeModuleDetails(array $w, InputInterface $input, OutputInterface $output, SymfonyStyle $io) {

        $module = new Module();

        $module
            ->setName($w['name'])
            ->setEntryURL($w['entryURL'])
            ->setDescription($w['description'])
            ->setActive($w['active'])
            ->setCategory($w['category'])
            ->setVersion($w['version'])
            ->setAuthor($w['author'])
            ->setUrl($w['url'])
            ->setType($w['type'])
        ;
        $actions = [];
        $permissions = [];
        foreach($w['actions'] as $r)
        {
            $action = new Action();
            $action
                ->setName($r['name'])
                ->setPrecedence($r['precedence'])
                ->setCategory($r['category'])
                ->setDescription($r['description'])
                ->setURLList($r['URLList'])
                ->setEntryURL($r['entryURL'])
                ->setEntrySidebar($r['entrySidebar'])
                ->setMenuShow($r['menuShow'])
                ->setDefaultPermissionAdmin($r['defaultPermissionAdmin'])
                ->setDefaultPermissionTeacher($r['defaultPermissionTeacher'])
                ->setDefaultPermissionStudent($r['defaultPermissionStudent'])
                ->setDefaultPermissionParent($r['defaultPermissionParent'])
                ->setDefaultPermissionSupport($r['defaultPermissionSupport'])
                ->setCategoryPermissionStaff($r['categoryPermissionStaff'])
                ->setCategoryPermissionStudent($r['categoryPermissionStudent'])
                ->setCategoryPermissionParent($r['categoryPermissionParent'])
                ->setCategoryPermissionOther($r['categoryPermissionOther'])
                ->setModule($module)
                ;

            foreach($r['permissions'] as $t) {
                $role = $this->em->getRepository(Role::class)->findOneByName($t);
                $permission = new Permission();
                $permission
                    ->setAction($action)
                    ->setRole($role);
                $permissions[] = $permission;
            }
            $actions[] = $action;
        }

        $mu = new ModuleUpgrade();
        $mu->setModule($module)->setVersion('Installation');

        $exitCode = 0;

        try {
            $this->em->beginTransaction();
            $this->em->persist($module);
            $this->em->persist($mu);
            foreach($actions as $action)
                $this->em->persist($action);
            foreach($permissions as $permission)
                $this->em->persist($permission);
            $this->em->flush();
            $this->em->commit();
        } catch (PDOException $e) {
            $this->em->rollback();
            $io->error($e->getMessage());
            $exitCode = 1;
        }

        if ($exitCode === 0) {
            $io->success('All Done for "' . $w['name']. '"');
        } else {
            $io->error(sprintf('Some errors occurred while installing the "%s" bundle to the Module Table.', $w['name']));
        }
        return $exitCode;
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

        if (null === $module || null === $this->em->getRepository(ModuleUpgrade::class)->findOneBy(['module' => $module, 'version' => 'Installation']))
            return false;

        return true;
    }
}