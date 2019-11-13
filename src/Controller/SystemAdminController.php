<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 20/07/2019
 * Time: 16:07
 */

namespace Kookaburra\SystemAdmin\Controller;

use App\Container\Container;
use App\Container\ContainerManager;
use App\Container\Panel;
use App\Entity\I18n;
use App\Entity\Setting;
use App\Entity\StringReplacement;
use App\Manager\VersionManager;
use App\Provider\ProviderFactory;
use App\Util\TranslationsHelper;
use Doctrine\DBAL\Driver\PDOException;
use Kookaburra\SystemAdmin\Form\DisplaySettingsType;
use Kookaburra\SystemAdmin\Form\EmailSettingsType;
use Kookaburra\SystemAdmin\Form\GoogleIntegationType;
use Kookaburra\SystemAdmin\Form\LocalisationSettingsType;
use Kookaburra\SystemAdmin\Form\MiscellaneousSettingsType;
use Kookaburra\SystemAdmin\Form\OrganisationSettingsType;
use Kookaburra\SystemAdmin\Form\PaypalSettingsType;
use Kookaburra\SystemAdmin\Form\SecuritySettingsType;
use Kookaburra\SystemAdmin\Form\SMSSettingsType;
use Kookaburra\SystemAdmin\Form\StringReplacementType;
use Kookaburra\SystemAdmin\Form\SystemSettingsType;
use Kookaburra\SystemAdmin\Manager\GoogleSettingManager;
use Kookaburra\SystemAdmin\Manager\LanguageManager;
use Kookaburra\SystemAdmin\Manager\MailerSettingsManager;
use Kookaburra\SystemAdmin\Manager\StringReplacementPagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SystemAdminController
 * @package App\Controller
 * @Route("/system_admin", name="system_admin__")
 */
class SystemAdminController extends AbstractController
{
    /**
     * systemSettings
     * @param Request $request
     * @Route("/system/{tabName}/settings/", name="system_settings")
     * @Route("/")
     * @Security("is_granted('ROLE_ROUTE', ['system_admin__system_settings'])")
     */
    public function systemSettings(Request $request, ContainerManager $manager, TranslatorInterface $translator, string $tabName = 'System')
    {
        $settingProvider = ProviderFactory::create(Setting::class);
        $settingProvider->getSettingsByScope('System');
        $container = new Container();
        // System Settings
        $form = $this->createForm(SystemSettingsType::class, null, ['action' => $this->generateUrl('system_admin__system_settings', ['tabName' => 'System'])]);

        if ($tabName === 'System' && $request->getContentType() === 'json') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request, $translator);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => $translator->trans('Your request failed due to a database error.')];
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('System');
        $container->addForm('System', $form->createView())->setTarget('formContent')->addPanel($panel);

        // Organisation Settings
        $form = $this->createForm(OrganisationSettingsType::class, null,
            [
                'action' => $this->generateUrl('system_admin__system_settings', ['tabName' => 'Organisation']),
                'attr' => [
                    'encType' => 'multipart/form-data',
                ],
            ]
        );

        if ($tabName === 'Organisation' && $request->getContentType() === 'json') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request, $translator);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => $translator->trans('Your request failed due to a database error.') . ' ' . $e->getMessage()];
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');
            $data['status'] = 'redirect';
            $data['redirect'] = $this->generateUrl('system_admin__system_settings', ['tabName' => 'Organisation']);
            $this->addFlash('success', 'return.success.0');
            return new JsonResponse($data, 200);
        }

        $panel = new Panel('Organisation');
        $container->addForm('Organisation', $form->createView())->addPanel($panel)->setSelectedPanel($tabName);

        // Security Settings
        $form = $this->createForm(SecuritySettingsType::class, null,
            [
                'action' => $this->generateUrl('system_admin__system_settings', ['tabName' => 'Security']),
            ]
        );

        if ($tabName === 'Security' && $request->getContentType() === 'json') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request, $translator);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => $translator->trans('Your request failed due to a database error.') . ' ' . $e->getMessage()];
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('Security');
        $container->addForm('Security', $form->createView())->addPanel($panel)->setSelectedPanel($tabName);

        // Localisation
        $form = $this->createForm(LocalisationSettingsType::class, null,
            [
                'action' => $this->generateUrl('system_admin__system_settings', ['tabName' => 'Localisation']),
            ]
        );

        if ($tabName === 'Localisation' && $request->getContentType() === 'json') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request, $translator);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => $translator->trans('Your request failed due to a database error.') . ' ' . $e->getMessage()];
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('Localisation');
        $container->addForm('Localisation', $form->createView())->addPanel($panel)->setSelectedPanel($tabName);

        // Miscellaneous
        $form = $this->createForm(MiscellaneousSettingsType::class, null,
            [
                'action' => $this->generateUrl('system_admin__system_settings', ['tabName' => 'Miscellaneous']),
            ]
        );

        if ($tabName === 'Miscellaneous' && $request->getContentType() === 'json') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request, $translator);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => $translator->trans('Your request failed due to a database error.') . ' ' . $e->getMessage()];
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');
            $data['status'] = 'redirect';
            $data['redirect'] = $this->generateUrl('system_admin__system_settings', ['tabName' => 'Miscellaneous']);
            $this->addFlash('success', 'return.success.0');

            return new JsonResponse($data, 200);
        }

        $panel = new Panel('Miscellaneous');
        $container->addForm('Miscellaneous', $form->createView())->addPanel($panel)->setSelectedPanel($tabName);

        // Finally Finished
        $manager->addContainer($container)->buildContainers();

        return $this->render('@KookaburraSystemAdmin/system_settings.html.twig');
    }

    /**
     * thirdParty
     * @param Request $request
     * @param ContainerManager $manager
     * @param TranslatorInterface $translator
     * @param string $tabName
     * @Route("/third/{tabName}/party/", name="third_party")
     * @IsGranted("ROLE_ROUTE"))
     */
    public function thirdParty(Request $request, ContainerManager $manager, TranslatorInterface $translator, string $tabName = 'Google')
    {
        $settingProvider = ProviderFactory::create(Setting::class);
        $container = new Container();
        $container->setTarget('formContent')->setSelectedPanel($tabName)->setApplication('ThirdParty');

        // Google
        $form = $this->createForm(GoogleIntegationType::class, null, ['action' => $this->generateUrl('system_admin__third_party', ['tabName' => 'Google'])]);

        if ($tabName === 'Google' && $request->getContentType() === 'json') {
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

        if ($tabName === 'PayPal' && $request->getContentType() === 'json') {
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

        if ($tabName === 'SMS' && $request->getContentType() === 'json') {
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

        if ($tabName === 'E-Mail' && $request->getContentType() === 'json') {
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

        return $this->render('@KookaburraSystemAdmin/third_party.html.twig');
    }

    /**
     * check
     * @param VersionManager $manager
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/check/", name="check")
     * @IsGranted("ROLE_ROUTE")
     */
    public function check(VersionManager $manager)
    {
        return $this->render('@KookaburraSystemAdmin/check.html.twig',
            [
                'manager' => $manager->setEm($this->getDoctrine()->getManager()),
            ]
        );
    }

    /**
     * systemSettings
     * @param Request $request
     * @Route("/display/settings/", name="display_settings")
     * @IsGranted("ROLE_ROUTE")
     */
    public function displaySettings(Request $request, ContainerManager $manager, TranslatorInterface $translator)
    {
        $settingProvider = ProviderFactory::create(Setting::class);

        // System Settings
        $form = $this->createForm(DisplaySettingsType::class, null, ['action' => $this->generateUrl('system_admin__display_settings')]);

        if ($request->getContentType() === 'json') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request, $translator);
            } catch (\Exception $e) {
                $data['errors'][] = ['class' => 'error', 'message' => $translator->trans('Your request failed due to a database error.')];
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');

            return new JsonResponse($data, 200);
        }

        $manager->singlePanel($form->createView());

        return $this->render('@KookaburraSystemAdmin/display_settings.html.twig');
    }

    /**
     * languageInstall
     * @param Request $request
     * @return RedirectResponse
     * @Route("/language/manage/", name="language_manage")
     * @IsGranted("ROLE_ROUTE")
     */
    public function languageManage(Request $request, LanguageManager $manager)
    {
        $langsInstalled = ProviderFactory::getRepository(I18n::class)->findBy(['installed' => 'Y'], ['code' => "ASC"]);
        $langsNotInstalled = ProviderFactory::getRepository(I18n::class)->findBy(['installed' => 'N'], ['code' => 'ASC']);

        $langFileMissing = 0;
        foreach($langsInstalled as $q=>$lang){
            if (! $lang->isInstalled()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($lang);
                $em->flush();
                unset($langsInstalled[$q]);
                $langFileMissing++;
            }
        }

        if ($langFileMissing > 0)
            $langsNotInstalled = ProviderFactory::getRepository(I18n::class)->findBy(['installed' => 'N'], ['code' => 'ASC']);

        return $this->render('@KookaburraSystemAdmin/language_manage.html.twig', [
            'installed' => $langsInstalled,
            'notInstalled' => $langsNotInstalled,
            'manager' => $manager,
            'translationPath' => $this->getProjectDir() . '/translations',
            'gVersion' => $this->getParameter('gibbon_version'),
        ]);
    }

    /**
     * languageSetDefault
     * @param I18n $i18n
     * @Route("/language/{i18n}/default/", name="language_default")
     * @Security("is_granted('ROLE_ROUTE', ['system_admin__language_manage'])")
     */
    public function languageSetDefault(I18n $i18n, SessionInterface $session)
    {
        $provider = ProviderFactory::create(I18n::class);
        $was = $provider->getRepository()->findOneBySystemDefault('Y');
        $was->setSystemDefault('N');
        $i18n->setSystemDefault('Y');
        $em = $this->getDoctrine()->getManager();
        $em->persist($was);
        $em->persist($i18n);
        $em->flush();
        $config = Yaml::parse(file_get_contents($this->getSettingFileName()));
        $config['parameters']['locale'] = $i18n->getCode();
        file_put_contents($this->getSettingFileName(), Yaml::dump($config, 8));
        $this->addFlash('success', 'Your request was completed successfully.');
        $session->set('i18n', $i18n->toArray());
        return $this->redirectToRoute('system_admin__language_manage');
    }

    /**
     * languageInstall
     * @param Request $request
     * @return RedirectResponse
     * @Route("/language/{i18n}/install/", name="language_install")
     * @Security("is_granted('ROLE_ROUTE', ['system_admin__language_manage'])")
     */
    public function languageInstall(I18n $i18n, Request $request, LanguageManager $manager)
    {
        $installed = $manager->i18nFileInstall($i18n);

        if ($installed) {
            $i18n->setInstalled('Y');
            $i18n->setVersion($this->getParameter('gibbon_version'));
            $em = $this->getDoctrine()->getManager();
            try {
                $em->persist($i18n);
                $em->flush();
                $updated = true;
            } catch (PDOException $e) {
                $updated = false;
            } catch (\PDOException $e) {
                $updated = false;
            }
        }

        if (!$installed) {
            $this->addFlash('error', 'The file transfer was not completed successfully.  Please try again.');
            return $this->redirectToRoute('system_admin__language_manage');
        }
        if (!$updated) {
            $this->addFlash('warning', 'Your request was successful, but some data was not properly saved.');
            return $this->redirectToRoute('system_admin__language_manage');
        }
        $this->addFlash('success', 'Your request was completed successfully.');
        return $this->redirectToRoute('system_admin__language_manage');
    }

    /**
     * stringReplacementEdit
     * @param Request $request
     * @param ContainerManager $manager
     * @param string|null $stringReplacement
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/string/replacement/{stringReplacement}/edit/", name="string_replacement_edit")
     * @IsGranted("ROLE_ROUTE")
     */
    public function stringReplacementEdit(Request $request, ContainerManager $manager, ?string $stringReplacement = 'Add')
    {
        $stringReplacement = $stringReplacement !== 'Add' ? ProviderFactory::getRepository(StringReplacement::class)->find($stringReplacement) : new StringReplacement();

        $form = $this->createForm(StringReplacementType::class, $stringReplacement, ['action' => $this->generateUrl('system_admin__string_replacement_edit', ['stringReplacement' => $stringReplacement->getId() ?: 'Add'])]);

        if ($request->getContentType() === 'json') {
            $content = json_decode($request->getContent(), true);

            $data = [];
            $form->submit($content);
            if ($form->isValid()) {

                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($stringReplacement);
                    $em->flush();
                    $data['errors'][] = ['class' => 'success', 'message' => TranslationsHelper::translate('Your request was completed successfully.', [], 'messages')];
                    $data['status'] = 'success';
                    $form = $this->createForm(StringReplacementType::class, $stringReplacement, ['action' => $this->generateUrl('system_admin__string_replacement_edit', ['stringReplacement' => $stringReplacement->getId() ?: 'Add'])]);
                } catch (PDOException $e) {
                    $data['errors'][] = ['class' => 'error', 'message' => TranslationsHelper::translate('Your request failed because your inputs were invalid.', [], 'messages')];
                    $data['status'] = 'error';
                }
            } else {
                $data['errors'][] = ['class' => 'error', 'message' => TranslationsHelper::translate('Your request failed due to a database error.', [], 'messages')];
                $data['status'] = 'error';
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');

            return JsonResponse::create($data, 200);
        }

        $manager->singlePanel($form->createView());

        return $this->render('@KookaburraSystemAdmin/string_replacement_edit.html.twig');
    }

    /**
     * stringReplacementManage
     * @param Request $request
     * @param string|null $stringReplacement
     * @Route("/string/replacement/manage/", name="string_replacement_manage")
     * @IsGranted("ROLE_ROUTE")
     */
    public function stringReplacementManage(Request $request, StringReplacementPagination $pagination)
    {
        $content = [];
        $provider = ProviderFactory::create(StringReplacement::class);
        $content = $provider->getPaginationResults($request->query->get('search'));
        $pagination->setContent($content)
            ->setPaginationScript();
        return $this->render('@KookaburraSystemAdmin/string_replacement_manage.html.twig',
            [
                'content' => $content,
                'search' => $request->query->get('search') ?: '',
            ]
        );
    }

    /**
     * stringReplacementDelete
     * @param StringReplacement $stringReplacement
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/string/replacement/{stringReplacement}/delete/", name="string_replacement_delete")
     * @IsGranted("ROLE_ROUTE")
     */
    public function stringReplacementDelete(StringReplacement $stringReplacement)
    {
        try {
            $em =$this->getDoctrine()->getManager();
            $em->remove($stringReplacement);
            $em->flush();
            $this->addFlash('success', 'Your request was completed successfully.');
        } catch (PDOException $e) {
            $this->addFlash('error', 'Your request failed due to a database error.');
        }

        return $this->forward(SystemAdminController::class . '::stringReplacementManage');
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