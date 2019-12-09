<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 26/10/2019
 * Time: 09:24
 */

namespace Kookaburra\SystemAdmin\Controller;

use App\Container\ContainerManager;
use App\Provider\ProviderFactory;
use App\Util\ReactFormHelper;
use App\Util\TranslationsHelper;
use Doctrine\DBAL\Driver\PDOException;
use Kookaburra\SystemAdmin\Entity\NotificationEvent;
use Kookaburra\SystemAdmin\Entity\NotificationListener;
use Kookaburra\SystemAdmin\Form\NotificationEventType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NotificationController
 * @package App\Controller
 * @Route("/system_admin", name="system_admin__")
 */

class NotificationController extends AbstractController
{

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
}