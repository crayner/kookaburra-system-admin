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
use App\Entity\NotificationEvent;
use App\Entity\NotificationListener;
use App\Entity\Setting;
use App\Entity\StringReplacement;
use App\Form\Entity\ImportControl;
use App\Manager\ExcelManager;
use App\Manager\VersionManager;
use App\Provider\ProviderFactory;
use App\Util\GlobalHelper;
use App\Util\ReactFormHelper;
use App\Util\TranslationsHelper;
use App\Util\UserHelper;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\QueryException;
use Kookaburra\SystemAdmin\Form\DisplaySettingsType;
use Kookaburra\SystemAdmin\Form\EmailSettingsType;
use Kookaburra\SystemAdmin\Form\GoogleIntegationType;
use Kookaburra\SystemAdmin\Form\ImportStep1Type;
use Kookaburra\SystemAdmin\Form\ImportStep2Type;
use Kookaburra\SystemAdmin\Form\ImportStep3Type;
use Kookaburra\SystemAdmin\Form\LocalisationSettingsType;
use Kookaburra\SystemAdmin\Form\MiscellaneousSettingsType;
use Kookaburra\SystemAdmin\Form\NotificationEventType;
use Kookaburra\SystemAdmin\Form\OrganisationSettingsType;
use Kookaburra\SystemAdmin\Form\PaypalSettingsType;
use Kookaburra\SystemAdmin\Form\SecuritySettingsType;
use Kookaburra\SystemAdmin\Form\SMSSettingsType;
use Kookaburra\SystemAdmin\Form\StringReplacementType;
use Kookaburra\SystemAdmin\Form\SystemSettingsType;
use Kookaburra\SystemAdmin\Manager\GoogleSettingManager;
use Kookaburra\SystemAdmin\Manager\ImportManager;
use Kookaburra\SystemAdmin\Manager\ImportReport;
use Kookaburra\SystemAdmin\Manager\ImportReportField;
use Kookaburra\SystemAdmin\Manager\LanguageManager;
use Kookaburra\SystemAdmin\Manager\MailerSettingsManager;
use Kookaburra\SystemAdmin\Manager\StringReplacementPagination;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
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
     * @IsGranted("ROLE_ROUTE")
     */
    public function systemSettings(Request $request, ContainerManager $manager, TranslatorInterface $translator, string $tabName = 'System')
    {
        $settingProvider = ProviderFactory::create(Setting::class);
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
     * notificationSettings
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/notification/settings/", name="notification_settings")
     * @IsGranted("ROLE_ROUTE")
     */
    public function notificationSettings()
    {
        $notificationProvider = ProviderFactory::create(NotificationEvent::class);

        return $this->render('@KookaburraSystemAdmin/notification_settings.html.twig',
            [
                'events' => $notificationProvider->selectAllNotificationEvents(),
            ]
        );
    }

    /**
     * notificationEventEdit
     * @param Request $request
     * @param NotificationEvent $event
     * @Route("/notification/{event}/edit/", name="notification_edit")
     * @IsGranted("ROLE_ROUTE")
     */
    public function notificationEventEdit(Request $request, NotificationEvent $event, ContainerManager $manager, ReactFormHelper $helper)
    {
        $form = $this->createForm(NotificationEventType::class, $event, ['action' => $this->generateUrl('system_admin__notification_edit', ['event' => $event->getId()]), 'listener_delete_route' => $this->generateUrl('system_admin__notification_listener_delete', ['listener' => '__id__', 'event' => '__event__'])]);

        if ($request->getContentType() === 'json') {
            $content = json_decode($request->getContent(), true);
            $form->submit($content);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                try {
                    $em->persist($event);
                    foreach ($event->getListeners() as $listener) {
                        $em->persist($listener);
                    }
                    $em->flush();
                    $em->refresh($event);
                    foreach ($event->getListeners() as $listener) {
                        $em->refresh($listener);
                    }
                    $form = $this->createForm(NotificationEventType::class, $event, ['action' => $this->generateUrl('system_admin__notification_edit', ['event' => $event->getId()]), 'listener_delete_route' => $this->generateUrl('system_admin__notification_listener_delete', ['listener' => '__id__', 'event' => '__event__'])]);
                    $data['errors'][] = ['class' => 'success', 'message' => TranslationsHelper::translate('Your request was completed successfully.')];
                } catch (PDOException $e) {
                    $data['errors'][] = ['class' => 'error', 'message' => TranslationsHelper::translate('Your request failed due to a database error.')];
                } catch (\PDOException $e) {
                    $data['errors'][] = ['class' => 'error', 'message' => TranslationsHelper::translate('Your request failed due to a database error.')];
                }
            } else {
                $data['errors'][] = ['class' => 'error', 'message' => TranslationsHelper::translate('Your request failed because your inputs were invalid.')];
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');

            return new JsonResponse($data, 200);

        }

        $manager->singlePanel($form->createView(), 'NotificationEvent');

        return $this->render('@KookaburraSystemAdmin/notification_edit.html.twig');
    }

    /**
     * notificationListenerDelete
     * @param NotificationEvent $event
     * @param NotificationListener $listener
     * @param ContainerManager $manager
     * @param ReactFormHelper $helper
     * @Route("/notification/{event}/listener/{listener}/delete/", name="notification_listener_delete")
     * @Security("is_granted('ROLE_ROUTE', ['system_admin__notification_edit'])");
     * @return JsonResponse
     */
    public function notificationListenerDelete(NotificationEvent $event, NotificationListener $listener, ContainerManager $manager, ReactFormHelper $helper)
    {
        $data = [];
        $data['errors'] = [];
        $data['form'] = [];
        $em = $this->getDoctrine()->getManager();
        if (!$event->getListeners()->contains($listener)) {
            $data['errors'][] = ['class' => 'error', 'message' => TranslationsHelper::translate('Your request failed because your inputs were invalid.', [], 'messages')];
            $data['status'] = 'error';
            return JsonResponse::create($data, 200);
        }

        try {
            $em->remove($listener);
            $em->flush();
        } catch (PDOException $e) {
            $data['errors'][] = ['class' => 'error', 'message' => TranslationsHelper::translate('Your request failed due to a database error.', [], 'messages')];
            $data['status'] = 'error';
            return JsonResponse::create($data, 200);
        }
        $em->refresh($event);
        $form = $this->createForm(NotificationEventType::class, $event, ['action' => $this->generateUrl('system_admin__notification_edit', ['event' => $event->getId()]), 'listener_delete_route' => $this->generateUrl('system_admin__notification_listener_delete', ['listener' => '__id__', 'event' => '__event__'])]);

        $manager->singlePanel($form->createView(), 'NotificationEvent');
        $data['form'] = $manager->getFormFromContainer('formContent', 'single');
        if ($data['errors'] === []) {
            $data['errors'][] = ['class' => 'success', 'message' => TranslationsHelper::translate('Your request was completed successfully.', [], 'messages')];
            $data['status'] = 'success';
        }

        //JSON Response required.
        return JsonResponse::create($data, 200);
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
     * @param ContainerManager $manager
     * @param string|null $stringReplacement
     * @Route("/string/replacement/manage/", name="string_replacement_manage")
     * @IsGranted("ROLE_ROUTE")
     */
    public function stringReplacementManage(Request $request, ContainerManager $manager, StringReplacementPagination $pagination)
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
     * manageImport
     * @Route("/import/manage/", name="import_manage")
     * @IsGranted("ROLE_ROUTE")
     */
    public function manageImport(ImportManager $manager)
    {
        $manager->loadImportReportList();

        return $this->render('@KookaburraSystemAdmin/Import/import_manage.html.twig',
            [
                'manager' => $manager,
            ]
        );
    }

    /**
     * exportRun
     * @param string $report
     * @param ImportManager $manager
     * @param ExcelManager $excel
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param bool $data
     * @param bool $all
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @Route("/export/{report}/{data}/run/{all}", name="export_run")
     * @IsGranted("ROLE_ROUTE")
     */
    public function exportRun(string $report, ImportManager $manager, ExcelManager $excel, Request $request, TranslatorInterface $translator, bool $data = false, bool $all = false)
    {
        $manager->setDataExport($data || true);
        $manager->setDataExportAll($all);
        $session = $request->getSession();

        $report = $manager->getImportReport($report);
        if (!$report instanceof ImportReport)
            return $this->render('components/error.html.twig',
                [
                    'error' => 'Your request failed because your inputs were invalid.',
                ]
            );

        if (!$report->isImportAccessible())
            return $this->render('components/error.html.twig',
                [
                    'error' => 'Your request failed because you do not have access to this action.',
                ]
            );


        //Create border styles
        $style_head_fill= array(
            'fill' => array('fillType' => Fill::FILL_SOLID, 'startColor' => array('rgb' => 'eeeeee'), 'endColor' => array('rgb' => 'eeeeee')),
            'borders' => array('top' => array('borderStyle' => Border::BORDER_THIN, 'color' => array('rgb' => '444444'), ), 'bottom' => array('borderStyle' => Border::BORDER_THIN, 'color' => array('rgb' => '444444'), )),
        );

        // Set document properties
        $excel->getProperties()->setCreator(UserHelper::getCurrentUser()->formatName())
            ->setLastModifiedBy(UserHelper::getCurrentUser()->formatName())
            ->setTitle($report->getDetail('name'))
        ;

        $excel->setActiveSheetIndex(0);

        $count = 0;
        $columnFields = $report->getFields();

        $columnFields = $columnFields->filter(function (ImportReportField $field) {
            return !$field->isFieldHidden();
        });

        // Create the header row
        foreach ($columnFields as $field) {
            $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($count) . '1', $translator->trans($field->getLabel()));
            $excel->getActiveSheet()->getStyle(GlobalHelper::num2alpha($count) . '1')->applyFromArray($style_head_fill);

            // Dont auto-size giant text fields
            if ($field->getArg('kind') === 'text') {
                $excel->getActiveSheet()->getColumnDimension(GlobalHelper::num2alpha($count))->setWidth(25);
            } else {
                $excel->getActiveSheet()->getColumnDimension(GlobalHelper::num2alpha($count))->setAutoSize(true);
            }

            // Add notes to column headings
            $info = ($field->isRequired()) ? "* required\n" : '';
            $info .= $this->renderView('@KookaburraSystemAdmin/field_type_component.html.twig',['type' => $field->readableFieldType()]) . "\n";
            $info .= $field->getArg('desc');
            $info = strip_tags($info);

            if (!empty($info)) {
                $excel->getActiveSheet()->getComment(GlobalHelper::num2alpha($count) . '1')->getText()->createTextRun($info);
            }

            $count++;
        }

        if ($manager->isDataExport()) {

            $data = [];
            $tableName = ucfirst($report->getDetail('table'));
            $query = $this->getDoctrine()->getManager()->createQueryBuilder();
            $query->from('\App\Entity\\' . $tableName, $report->getJoinAlias($tableName));

            foreach ($report->getJoin() as $fieldName => $join) {
                if (!$join->isPrimary()) {
                    $type = $join->getJoinType();
                    if ($join->getWith() === false)
                        $query->$type($report->getJoinAlias($join->getTable()) . '.' . $join->getReference(), $join->getAlias());
                    else
                        $query->$type($report->getJoinAlias($join->getTable()) . '.' . $join->getReference(), $join->getAlias(), Join::WITH, $join->getWith());
                }
            }

            foreach($report->getDetail('with') as $item)
                $query->andWhere($item);

            $select = [];
            $additional = [];
            foreach ($report->getFields() as $name=>$field) {
                if (!$field->getArg('serialise')) {
                    $w = $field->getSelect() . ' AS ' . $name;
                    $select[] = $w;
                } elseif (is_string($field->getArg('serialise')))
                    $additional[] = $field->getLabel();
            }

            $query->select($select);

            if (!$manager->isDataExportAll() && !in_array($tableName, ['SchoolYear', 'SchoolYearSpecialDay']))
            {
                // Optionally limit all exports to the current school year by default, to avoid massive files
                $schoolYear = $report->getTablesUsed();
                $field = $report->findFieldByArg('filter', 'schoolyear');
                if ($field && in_array('SchoolYear', $report->getTablesUsed()) && !$field->isFieldReadOnly()) {
                    $data['schoolYear'] = $session->get('schoolYearCurrent')->getId();
                    $query->andWhere($report->getJoinAlias('SchoolYear') . '.id = :schoolYear');
                }
            }

            $i = 0;
            foreach($report->getOrderBy() as $name=>$direction)
            {
                if ($i === 0)
                    $query->orderBy($name, $direction === 'DESC' ? 'DESC' : 'ASC');
                else
                    $query->addOrderBy($name, $direction === 'DESC' ? 'DESC' : 'ASC');
                $i = 1;
            }

            try {
                $result = $query->setParameters(array_merge(($data ?: []), $report->getFixedData()))->getQuery()->getResult();
                // dd($query,$result, $query->getQuery()->getSql());
            } catch (QueryException $e) {
                throw $e;
            }

            // Continue if there's data
            if (count($result) > 0) {

                $rowCount = 2;
                foreach ($result as $row) {
                    $row = $report->parseData($row);
                    $i = 0;
                    foreach ($row as $name=>$value) {
                        if (!$report->isHiddenField($name)) {
                            switch ($report->getFieldFilter($name)) {
                                case 'date':
                                    if (is_string($value)) {
                                        $value = \date_create_from_format($request->getSession()->get('i18n')['dateFormatPHP'] . ' H:i:s', $value . ' 00:00:00');
                                    }
                                    if ($value instanceof \DateTime)
                                        $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, $value->format('Y-m-d'));
                                    else
                                        $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, '');
                                    break;
                                case 'time':
                                    $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, null === $value ? '' : $value->format('H:i:s'));
                                    break;
                                case 'timestamp':
                                    $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, null === $value ? '' : $value->format('Y-m-d H:i:s'));
                                    break;
                                case 'yesno':
                                    $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, strtolower($value) === 'y' ? 'Yes' : 'No');
                                    break;
                                case 'array':
                                    dd($value, $row, $result);
                                    $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, strtolower($value) === 'y' ? 'Yes' : 'No');
                                    break;
                                case 'year_group_list':
                                    $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, implode(',', $report->getField($name)->transformYearGroups(explode(',', $value))));
                                    break;
                                case 'role_list':
                                    $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, implode(',', $report->getField($name)->transformRoles($value)));
                                    break;
                                case 'string':
                                case 'numeric':
                                case 'url':
                                case 'schoolyear':
                                case 'country':
                                case 'enum':
                                case 'html':
                                    $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, (string) $value);
                                    break;
                                default:
                                    dd($report->getFieldFilter($name));
                            }
                        }
                    }
                    $rowCount++;
                }
            }
        }

        $filename = ($manager->isDataExport()) ? 'DataExport' . '-' . $report->getDetails()->getName() : 'DataStructure' . '-' . $report->getDetails()->getName();

        $excel->setFileName($filename);

        // FINALIZE THE DOCUMENT SO IT IS READY FOR DOWNLOAD
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $excel->setActiveSheetIndex(0);

        $excel->exportWorksheet();
    }

    /**
     * importRun
     * @param string $report
     * @param ImportManager $manager
     * @param ExcelManager $excel
     * @param Request $request
     * @Route("/import/{report}/run/{step}", name="import_run")
     * @IsGranted("ROLE_ROUTE")
     */
    public function importRun(string $report, ImportManager $manager, Request $request, int $step = 1)
    {
        $memoryStart = memory_get_usage();
        $timeStart = microtime(true);
        $report = $manager->getImportReport($report);
        $importControl = new ImportControl();

        if ($step === 1) {
            $form = $this->createForm(ImportStep1Type::class, $importControl, ['action' => $this->generateUrl('system_admin__import_run', ['report' => $report->getDetails()->getName(), 'step' => $step + 1]), 'importReport' => $report]);
        } elseif ($step === 2) {
            $form = $this->createForm(ImportStep1Type::class, $importControl, ['action' => $this->generateUrl('system_admin__import_run', ['report' => $report->getDetails()->getName(), 'step' => $step]), 'importReport' => $report]);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $form = $this->createForm(ImportStep2Type::class, $importControl, [
                    'action' => $this->generateUrl('system_admin__import_run', ['report' => $report->getDetails()->getName(), 'step' => $step + 1]),
                    'importReport' => $report
                ]);
                $manager->prepareStep2($report, $importControl, $form, $request);
            } else {
                $step = 1;
            }
        } elseif ($step === 3) {
            $form = $this->createForm(ImportStep2Type::class, $importControl, [
                'action' => $this->generateUrl('system_admin__import_run', ['report' => $report->getDetails()->getName(), 'step' => $step]),
                'importReport' => $report
            ]);
            $manager->prepareStep2($report, $importControl, $form, $request);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $manager->prepareStep3($report, $importControl, $form, $request);
                $form = $this->createForm(ImportStep3Type::class, $importControl, [
                    'action' => $this->generateUrl('system_admin__import_run', ['report' => $report->getDetails()->getName(), 'step' => $step + 1]),
                    'importReport' => $report
                ]);
            } else {
                $step = 2;
            }
        } elseif ($step === 4) {
            $form = $this->createForm(ImportStep3Type::class, $importControl, [
                'action' => $this->generateUrl('system_admin__import_run', ['report' => $report->getDetails()->getName(), 'step' => 4]),
                'importReport' => $report
            ]);
            $form->handleRequest($request);
            if ($form->isValid()){
                $manager->prepareStep3($report, $importControl, $form, $request, true);
                if ($form->get('ignoreErrors') === '1')
                    $this->addFlash('warning', 'Imported with errors ignored.');
            }
        }

        return $this->render('@KookaburraSystemAdmin/Import/import_run.html.twig',
            [
                'report' => $report,
                'manager' => $manager,
                'step' => $step,
                'form' => $form->createView(),
                'executionTime' => mb_substr(microtime(true) - $timeStart, 0, 6),
                'memoryUsage' => $manager->readableFileSize(max(0, memory_get_usage() - $memoryStart)),
            ]
        );
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