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

use App\Migrations\SqlLoadTrait;
use App\Util\GlobalHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Kookaburra\SystemAdmin\Entity\Action;
use Kookaburra\SystemAdmin\Entity\Module;
use Kookaburra\SystemAdmin\Entity\ModuleUpgrade;
use Kookaburra\SystemAdmin\Entity\NotificationEvent;
use Kookaburra\SystemAdmin\Entity\Permission;
use Kookaburra\SystemAdmin\Entity\Role;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
     * @var string
     */
    protected static $defaultName = 'kookaburra:module:install';

    /**
     * ModuleInstallCommand constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em, GlobalHelper $helper)
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
            $io->text('Checking bundle <info>'.(isset($this->version['name']) ? $this->version['name'] : $bundle->getBasename()).'</info>.');
            // do the installation stuff

            if (!$this->isModuleInstalled($bundle)) {
                $io->text('Installing database for bundle <info>'.(isset($this->version['name']) ? $this->version['name'] : $bundle->getBasename()).'</info>.');

                $io->newLine();

                if (is_file($bundle->getRealpath() . '/src/Resources/migration/installation.sql')) {
                    $this->getSql($bundle->getRealpath() . '/src/Resources/migration/installation.sql');
                    try {
                        $this->em->beginTransaction();
                        foreach ($this->getSqlContent() as $sql) {
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
                    if (isset($version['module']))
                        $exitCode += $this->writeModuleDetails($version['module'], $input, $output, $io);

                    if (isset($version['events']))
                        $exitCode += $this->writeEventDetails($version['events'], $input, $output, $io);
                }
                $io->success('Installation completed and database created for bundle "' . (isset($this->version['name']) ? $this->version['name'] : $bundle->getBasename()) . '"');
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
        $io->text(sprintf('Creating Module / Action / Permission entries for <info>%s</info> bundle.', $this->version['name']));

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

        if ($exitCode > 0) {
            $io->error(sprintf('Some errors occurred while installing the "%s" bundle to the Module Table.', $w['name']));
        }

        $this->setModule($module);

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

    /**
     * writeModuleDetails
     * @param array $w
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    private function writeEventDetails(array $w, InputInterface $input, OutputInterface $output, SymfonyStyle $io): int
    {
        $module = $this->getModule();
        if (null === $module || !is_array($w))
        {
            $io->error(sprintf('Not able to create events for "%s" bundle.', $this->version['name']));
            return 1;
        }
        $io->text(sprintf('Creating Notification Events for <info>%s</info> bundle.', $this->version['name']));
        $actions = new ArrayCollection($this->em->getRepository(Action::class)->findBy(['module' => $module]));

        try {
            $this->em->beginTransaction();
            foreach ($w as $name => $item) {
                $event = new NotificationEvent();
                $action = $actions->filter(function ($action) use ($item, $module) {
                    return $action->getName() === $item['action'] && $action->getModule() === $module;
                });

                $event->setEvent($name)
                    ->setModule($module)
                    ->setAction($action->first())
                    ->setType($item['type'])
                    ->setScopes($item['scopes'])
                    ->setActive($item['active']);
                $this->em->persist($event);
            }
            $this->em->flush();
            $this->em->commit();
        } catch (PDOException $e) {
            $this->em->rollback();
            $io->error($e->getMessage());
            return 1;
        }

        return 0;
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
}