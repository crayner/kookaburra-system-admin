<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 16/10/2019
 * Time: 09:07
 */

namespace Kookaburra\SystemAdmin\Manager;

use Kookaburra\SystemAdmin\Entity\Module;
use App\Manager\MessageManager;
use App\Provider\ProviderFactory;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\PDOException;
use Kookaburra\SystemAdmin\Entity\ModuleUpgrade;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class ModuleUpdateManager
{
    /**
     * @var Module
     */
    private $module;

    /**
     * @var array
     */
    private $version;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * ModuleUpdateManager constructor.
     * @param MessageManager $messageManager
     * @throws DBALException
     */
    public function __construct(MessageManager $messageManager)
    {
        $this->messageManager = $messageManager;

        $em = ProviderFactory::getEntityManager();
        $sql = "SHOW TABLES LIKE '%ModuleUpgrade'";
        $x = $em->getConnection()->query($sql)->fetchAll();
        if ([] === $x) {
            $this->setModule(ProviderFactory::getRepository(Module::class)->findOneByName('System Admin'));
            $update = Yaml::parse(file_get_contents($this->getModulePath() . '/src/Resources/migration/moduleUpgrade.yaml'));
            try {
                $em->getConnection()->beginTransaction();
                foreach($update['up'] as $sql)
                    $em->getConnection()->exec($sql);
                $em->getConnection()->commit();
            } catch (PDOException $e) {
                $em->getConnection()->rollback();
                $this->getMessageManager()->add('error', $e->getMessage());
            } catch (DBALException $e) {
                $em->getConnection()->rollback();
                $this->getMessageManager()->add('error', $e->getMessage());
            }
        }
    }

    /**
     * @return Module
     */
    public function getModule(): Module
    {
        return $this->module;
    }

    /**
     * Module.
     *
     * @param Module $module
     * @return ModuleUpdateManager
     */
    public function setModule(Module $module): ModuleUpdateManager
    {
        $this->module = $module;
        return $this;
    }

    /**
     * upgradeModule
     */
    public function upgradeModule()
    {
        if ($this->isInstalled()) {
            $em = ProviderFactory::getEntityManager();
            if (version_compare($this->getModule()->getVersion(), $this->getVersion()['version'], '<')) {
                $this->module->setVersion($this->getVersion()['version']);
                $em->persist($this->module);
                $em->flush();
            }

            $current = $this->getModule()->getUpgradeLogs()->count() > 0 ? $this->getModule()->getUpgradeLogs()->first()->getVersion() : '0';
            $finder = new Finder();
            $files = $finder->files()->in($this->getModulePath() . '/src/Resources/migration')->name(['Version*.yaml'])->depth(0)->sort(function ($a, $b) { return strcmp($a->getRealpath(), $b->getRealpath()); });

            foreach($files as $file) {
                $content = Yaml::parse(file_get_contents($file->getRealPath()));
                if ($content['version'] > $current) {
                    // Do the UP
                    $ok = true;
                    try {
                        $em->beginTransaction();
                        foreach ($content['up'] as $sql)
                            $em->getConnection()->exec($sql);
                        $em->commit();
                    } catch (PDOException $e) {
                        $em->rollback();
                        $this->getMessageManager()->add('error', $e->getMessage());
                        $ok = false;
                    } catch (DBALException $e) {
                        $em->rollback();
                        $this->getMessageManager()->add('error', $e->getMessage());
                        $ok = false;
                    }

                    if ($ok) {
                        $upgrade = new ModuleUpgrade();
                        $upgrade->setModule($this->getModule())->setVersion($content['version']);
                        $em->persist($upgrade);
                        $em->flush();
                        $this->getMessageManager()->add('success', 'Your request was completed successfully.');
                    }
                }
            }
        }
    }

    /**
     * getModulePath
     * @return string
     */
    private function getModulePath()
    {
        $name = strtolower(str_replace(' ', '-', $this->getModule()->getName()));
        return realpath(__DIR__ . '/../../../../kookaburra/' . $name);
    }

    /**
     * getVersion
     * @return array
     */
    public function getVersion(): array
    {
        if (null === $this->version) {
            $this->version = Yaml::parse(file_get_contents($this->getModulePath() . '/src/Resources/config/version.yaml'));
        }
        return $this->version;
    }

    /**
     * isInstalled
     * @return bool
     */
    private function isInstalled(): bool
    {
        return is_string($this->getModulePath()) && is_array($this->getVersion());
    }

    /**
     * getMessageManager
     * @return MessageManager
     */
    public function getMessageManager(): MessageManager
    {
        return $this->messageManager;
    }

    /**
     * getAllModules
     */
    public function getAllModules()
    {
        $content = ProviderFactory::getRepository(Module::class)->findBy([],['name' => 'ASC']);
        foreach($content as $module)
        {
            if ($this->setModule($module)->isInstalled()) {
                $module->setUpdateRequired($this->isUpdateRequired());
            }
            if ($this->getModulePath() !== false && !$this->isInstalled()) {
                $module->setUpdateRequired(true);
            }
        }
        return $content;
    }

    /**
     * isUpdateRequired
     * @return bool
     */
    private function isUpdateRequired(): bool
    {
        if (version_compare($this->getModule()->getVersion(), $this->getVersion()['version'], '<'))
            return true;

        if (!is_dir($this->getModulePath() . '/src/Resources/migration'))
            return false;

        $version = $this->getAvailableModuleUpgradeVersion();
        if ('' === $version)
            return false;

        if ($this->getModule()->getUpgradeLogs()->count() === 0 || $version < $this->getModule()->getUpgradeLogs()->first()->getVersion())
            return true;

        return false;
    }

    /**
     * getAvailableModuleUpgradeVersion
     * @return string
     */
    private function getAvailableModuleUpgradeVersion(): string
    {
        $finder = new Finder();
        $files = $finder->files()->in($this->getModulePath() . '/src/Resources/migration')->name(['Version*.yaml'])->depth(0)->sort(function ($a, $b) { return strcmp($b->getRealpath(), $a->getRealpath()); });

        if ($finder->hasResults() === false)
            return false;

        foreach($files as $file){
            return str_replace(['Version','.yaml'],'', $file->getBasename());
        }
    }

    /**
     * deleteModule
     */
    public function deleteModule()
    {
        if ($this->isInstalled()) {
            $em = ProviderFactory::getEntityManager();

            $finder = new Finder();
            $files = $finder->files()->in($this->getModulePath() . '/src/Resources/migration')->name(['Version*.yaml'])->depth(0)->sort(function ($a, $b) { return strcmp($b->getRealpath(), $a->getRealpath()); });

            $ok = true;
            foreach($files as $file) {
                $content = Yaml::parse(file_get_contents($file->getRealPath()));
                try {
                    $em->beginTransaction();
                    foreach ($content['down'] as $sql)
                        $em->getConnection()->exec($sql);
                    $em->commit();
                } catch (PDOException $e) {
                    $em->rollback();
                    $this->getMessageManager()->add('error', $e->getMessage());
                    $ok = false;
                } catch (DBALException $e) {
                    $em->rollback();
                    $this->getMessageManager()->add('error', $e->getMessage());
                    $ok = false;
                }
                break;
            }



            if (is_file($this->getModulePath() . '/src/Manager/Installation.php')) {
                $name = '\Kookaburra\\' . str_replace(' ', '', $this->getModule()->getName()) . '\Manager\Installation';
                $installer = new $name();
                if (class_implements($installer, ModuleInstallationInterface::class)) {
                    $installer->down();
                }
            }
            if ($ok) {
                ProviderFactory::getRepository(ModuleUpgrade::class)->deleteModuleRecords($this->getModule());
                $this->version['installedOn'] = false;
                $this->writeVersionFile();
                $em->remove($this->getModule());
                $em->flush();
            }
        }
    }

    /**
     * writeVersionFile
     */
    private function writeVersionFile()
    {
        file_put_contents($this->getModulePath(). '/src/Resources/config/version.yaml', Yaml::dump($this->getVersion(), 8));
    }

    /**
     * isSymfonyBundle
     * @return bool
     */
    public function isSymfonyBundle(): bool
    {
        return $this->getModulePath() ? true : false ;
    }
}