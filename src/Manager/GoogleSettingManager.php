<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 7/09/2019
 * Time: 14:35
 */

namespace Kookaburra\SystemAdmin\Manager;

use App\Entity\Setting;
use App\Provider\ProviderFactory;
use App\Provider\SettingProvider;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

class GoogleSettingManager
{
    /**
     * @var SettingProvider
     */
    private $provider;

    /**
     * GoogleSettingManager constructor.
     * @param SettingProvider $provider
     */
    public function __construct()
    {
        $this->provider = ProviderFactory::create(Setting::class);
    }

    /**
     * handleGoogleSecretsFile
     * @param Request $request
     * @param TranslatorInterface $translator
     * @return array
     */
    public function handleGoogleSecretsFile(FormInterface $form, Request $request, TranslatorInterface $translator)
    {
        $fileName = realpath($form->get('clientSecretFile')->getData()) ?: realpath($this->getProjectDir() . '/public' .$form->get('clientSecretFile')->getData()) ?: '';
        if (is_file($fileName)) {
            $content = json_decode($request->getContent(), true);
            $file = new File($fileName, true);
            try {
                $secret = json_decode(file_get_contents($file->getRealPath()), true);
            } catch (\Exception $e) {
                return ['class' => 'error', 'message' => $translator->trans('Your request failed due to a file transfer issue.', [], 'kookaburra')];
            }
            unlink($file->getRealPath());
            $config = Yaml::parse(file_get_contents($this->getSettingFileName()));

            $config['parameters']['google_api_key'] = $content['googleSettings']['System__googleDeveloperKey'];
            $config['parameters']['google_client_id'] = $secret['web']['client_id'];
            $config['parameters']['google_client_secret'] = $secret['web']['client_secret'];

            dump($secret['web'], $content);
            $providers = ProviderFactory::create(Setting::class);
            $providers->setSettingByScope('System', 'googleClientName', $secret['web']['project_id']);
            $providers->setSettingByScope('System', 'googleRedirectUri', $config['parameters']['absoluteURL'].'/security/oauth2callback/');
            $providers->setSettingByScope('System', 'googleClientID', $secret['web']['client_id']);
            $providers->setSettingByScope('System', 'googleClientSecret', $secret['web']['client_secret']);

            file_put_contents($this->getSettingFileName(), Yaml::dump($config, 8));
            return ['class' => 'info', 'message' => $translator->trans('Your requested included a valid Google Secret File.  The information was successfully stored.', [], 'kookaburra')];
        } else {
            $content = json_decode($request->getContent(), true);
            $config = Yaml::parse(file_get_contents($this->getSettingFileName()));

            $config['parameters']['google_api_key'] = $content['googleSettings']['System__googleDeveloperKey'];

            file_put_contents($this->getSettingFileName(), Yaml::dump($config, 8));
            return ['class' => 'info', 'message' => $translator->trans('Your requested did not included a valid Google Secret File. All other Google changes where saved.', [], 'kookaburra')];

        }
    }

    /**
     * getProjectDir
     * @return string
     */
    private function getProjectDir(): string
    {
        return realpath(__DIR__ . '/../../../../..');
    }
    /**
     * getSettingFileName
     * @return string
     */
    private function getSettingFileName(): string 
    {
        return realpath($this->getProjectDir() . '/config/packages/kookaburra.yaml');
    }
}