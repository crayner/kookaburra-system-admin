<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 * (c) 2020 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 26/03/2020
 * Time: 13:31
 */

namespace Kookaburra\SystemAdmin\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Entity\Setting;
use App\Manager\PageManager;
use App\Provider\ProviderFactory;
use App\Util\TranslationsHelper;
use Kookaburra\SystemAdmin\Form\EmailSettingsType;
use Kookaburra\SystemAdmin\Form\GoogleIntegationType;
use Kookaburra\SystemAdmin\Form\PaypalSettingsType;
use Kookaburra\SystemAdmin\Form\SMSSettingsType;
use Kookaburra\SystemAdmin\Manager\GoogleSettingManager;
use Kookaburra\SystemAdmin\Manager\MailerSettingsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ThirdPartyController
 * @package Kookaburra\SystemAdmin\Controller
 */
class ThirdPartyController extends AbstractController
{

    /**
     * thirdParty
     * @param PageManager $pageManager
     * @param ContainerManager $manager
     * @param TranslatorInterface $translator
     * @param string $tabName
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/third/{tabName}/party/", name="third_party")
     * @IsGranted("ROLE_ROUTE"))
     */
    public function thirdParty(PageManager $pageManager, ContainerManager $manager, TranslatorInterface $translator, string $tabName = 'Google')
    {
        if ($pageManager->isNotReadyForJSON()) return $pageManager->getBaseResponse();
        $request = $pageManager->getRequest();
        TranslationsHelper::setDomain('SystemAdmin');
        $manager->setTranslationDomain('SystemAdmin');

        $settingProvider = ProviderFactory::create(Setting::class);
        $container = new Container();
        $container->setTarget('formContent')->setSelectedPanel($tabName)->setApplication('ThirdParty');

        // Google
        $form = $this->createForm(GoogleIntegationType::class, null, ['action' => $this->generateUrl('system_admin__third_party', ['tabName' => 'Google'])]);

        if ($tabName === 'Google' && $request->getContent() !== '') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request, $translator);
                $gm = new GoogleSettingManager();
                $data['errors'][] = $gm->handleGoogleSecretsFile($form, $request, $translator);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => $translator->trans('Your request failed due to a database error.') . ' ' . $e->getMessage()];
            }

            $form = $this->createForm(GoogleIntegationType::class, null, ['action' => $this->generateUrl('system_admin__third_party', ['tabName' => 'Google'])]);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('Google');
        $container->addForm('Google', $form->createView())->addPanel($panel);

        // PayPal
        $form = $this->createForm(PaypalSettingsType::class, null, ['action' => $this->generateUrl('system_admin__third_party', ['tabName' => 'PayPal'])]);

        if ($tabName === 'PayPal' && $request->getContent() !== '') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request, $translator);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => $translator->trans('Your request failed due to a database error.')];
            }

            $form = $this->createForm(PaypalSettingsType::class, null, ['action' => $this->generateUrl('system_admin__third_party', ['tabName' => 'PayPal'])]);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('PayPal');
        $container->addForm('PayPal', $form->createView())->addPanel($panel);

        // SMS
        $form = $this->createForm(SMSSettingsType::class, null, ['action' => $this->generateUrl('system_admin__third_party', ['tabName' => 'SMS'])]);

        if ($tabName === 'SMS' && $request->getContent() !== '') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request, $translator);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => $translator->trans('Your request failed due to a database error.')];
            }

            $form = $this->createForm(SMSSettingsType::class, null, ['action' => $this->generateUrl('system_admin__third_party', ['tabName' => 'SMS'])]);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('SMS');
        $container->addForm('SMS', $form->createView())->addPanel($panel);

        // E-Mail
        $form = $this->createForm(EmailSettingsType::class, null, ['action' => $this->generateUrl('system_admin__third_party', ['tabName' => 'E-Mail'])]);

        if ($tabName === 'E-Mail' && $request->getContent() !== '') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request, $translator);
                $msm = new MailerSettingsManager();
                $msm->handleMailerDsn($request);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => $translator->trans('Your request failed due to a database error.')];
            }

            $form = $this->createForm(EmailSettingsType::class, null, ['action' => $this->generateUrl('system_admin__third_party', ['tabName' => 'E-Mail'])]);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('E-Mail');
        $container->addForm('E-Mail', $form->createView())->addPanel($panel);

        // Finally Finished
        $manager->addContainer($container)->buildContainers();

        return $pageManager->createBreadcrumbs('Third Party Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

}