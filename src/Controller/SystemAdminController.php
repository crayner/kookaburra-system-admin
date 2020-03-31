<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
use App\Manager\PageManager;
use App\Manager\VersionManager;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
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
use Kookaburra\SystemAdmin\Form\SystemSettingsType;
use Kookaburra\SystemAdmin\Manager\GoogleSettingManager;
use Kookaburra\SystemAdmin\Manager\LanguageManager;
use Kookaburra\SystemAdmin\Manager\MailerSettingsManager;
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
 */
class SystemAdminController extends AbstractController
{
    /**
     * systemSettings
     * @param PageManager $pageManager
     * @param ContainerManager $manager
     * @param TranslatorInterface $translator
     * @param string $tabName
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/system/{tabName}/settings/", name="system_settings")
     * @Route("/", name="default")
     * @Security("is_granted('ROLE_ROUTE', ['system_admin__system_settings'])")
     */
    public function systemSettings(PageManager $pageManager, ContainerManager $manager, TranslatorInterface $translator, string $tabName = 'System')
    {
        if ($pageManager->isNotReadyForJSON()) return $pageManager->getBaseResponse();
        $request = $pageManager->getRequest();

        $settingProvider = ProviderFactory::create(Setting::class);
        $settingProvider->getSettingsByScope('System');
        $container = new Container();
        $manager->setTranslationDomain('SystemAdmin');
        // System Settings
        $form = $this->createForm(SystemSettingsType::class, null, ['action' => $this->generateUrl('system_admin__system_settings', ['tabName' => 'System'])]);

        if ($tabName === 'System' && $request->getContent() !== '') {
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

        if ($tabName === 'Organisation' && $request->getContent() !== '') {
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

        if ($tabName === 'Security' && $request->getContent() !== '') {
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

        if ($tabName === 'Localisation' && $request->getContent() !== '') {
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

        if ($tabName === 'Miscellaneous' && $request->getContent() !== '') {
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

        return $pageManager->createBreadcrumbs('System Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * check
     * @param VersionManager $manager
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/check/", name="check")
     * @IsGranted("ROLE_ROUTE")
     */
    public function check(VersionManager $manager, PageManager $pageManager)
    {
        if ($pageManager->isNotReadyForJSON()) return $pageManager->getBaseResponse();

        return $pageManager->createBreadcrumbs('system Check')
            ->render(['content' => $this->renderView('@KookaburraSystemAdmin/check.html.twig', [
                'manager' => $manager->setEm($this->getDoctrine()->getManager()),
            ] )]);
    }

    /**
     * systemSettings
     * @param PageManager $pageManager
     * @param ContainerManager $manager
     * @param TranslatorInterface $translator
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @Route("/display/settings/", name="display_settings")
     * @IsGranted("ROLE_ROUTE")
     */
    public function displaySettings(PageManager $pageManager, ContainerManager $manager, TranslatorInterface $translator)
    {
        if ($pageManager->isNotReadyForJSON()) return $pageManager->getBaseResponse();
        $request = $pageManager->getRequest();

        $settingProvider = ProviderFactory::create(Setting::class);

        // System Settings
        $form = $this->createForm(DisplaySettingsType::class, null, ['action' => $this->generateUrl('system_admin__display_settings')]);

        if ($request->getContent() !== '') {
            $data = [];
            try {
                $data['errors'] = $settingProvider->handleSettingsForm($form, $request, $translator);
            } catch (\Exception $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
            }

            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer('formContent', 'single');

            return new JsonResponse($data, 200);
        }

        $manager->singlePanel($form->createView());

        return $pageManager->createBreadcrumbs('Display Settings')
            ->render(['containers' => $manager->getBuiltContainers()]);

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
        $this->addFlash('success', 'return.success.0');
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
        $this->addFlash('success', 'return.success.0');
        return $this->redirectToRoute('system_admin__language_manage');
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