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
 * Date: 26/10/2019
 * Time: 09:24
 */

namespace Kookaburra\SystemAdmin\Controller;

use App\Container\ContainerManager;
use App\Manager\PageManager;
use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use App\Util\ReactFormHelper;
use App\Util\TranslationsHelper;
use Doctrine\DBAL\Driver\PDOException;
use Kookaburra\SystemAdmin\Entity\NotificationEvent;
use Kookaburra\SystemAdmin\Entity\NotificationListener;
use Kookaburra\SystemAdmin\Form\NotificationEventType;
use Kookaburra\SystemAdmin\Manager\Hidden\NotificationEventHandler;
use Kookaburra\SystemAdmin\Pagination\NotificationEventPagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class NotificationController
 * @package App\Controller
 */
class NotificationController extends AbstractController
{

    /**
     * notificationEvents
     * @Route("/notification/events/", name="notification_events")
     * @IsGranted("ROLE_ROUTE")
     * @param PageManager $pageManager
     * @param NotificationEventPagination $pagination
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function notificationEvents(PageManager $pageManager, NotificationEventPagination $pagination)
    {
        if ($pageManager->isNotReadyForJSON()) return $pageManager->getBaseResponse();

        $notificationProvider = ProviderFactory::create(NotificationEvent::class);

        $pagination->setContent($notificationProvider->selectAllNotificationEvents())
            ->setPaginationScript();

        return $pageManager->createBreadcrumbs('Notification Events')
            ->render(['pagination' => $pagination->toArray()]);
    }

    /**
     * notificationEventEdit
     * @param PageManager $pageManager
     * @param NotificationEvent $event
     * @param ContainerManager $manager
     * @param ReactFormHelper $helper
     * @Route("/notification/{event}/edit/", name="notification_edit")
     * @IsGranted("ROLE_ROUTE")
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function notificationEventEdit(PageManager $pageManager, NotificationEvent $event, ContainerManager $manager, ReactFormHelper $helper)
    {
        if ($pageManager->isNotReadyForJSON()) return $pageManager->getBaseResponse();
        $request = $pageManager->getRequest();

        $form = $this->createForm(NotificationEventType::class, $event, ['action' => $this->generateUrl('system_admin__notification_edit', ['event' => $event->getId()]), 'listener_delete_route' => $this->generateUrl('system_admin__notification_listener_delete', ['listener' => '__id__', 'event' => '__event__'])]);

        if ($request->getContent() !== '') {
            $handler = new NotificationEventHandler();
            $data = $handler->handleRequest($request, $form, $event);
            if ($data['status'] === 'success')
                $form = $this->createForm(NotificationEventType::class, $event, ['action' => $this->generateUrl('system_admin__notification_edit', ['event' => $event->getId()]), 'listener_delete_route' => $this->generateUrl('system_admin__notification_listener_delete', ['listener' => '__id__', 'event' => '__event__'])]);
            $manager->singlePanel($form->createView());
            $data['form'] = $manager->getFormFromContainer();

            return new JsonResponse($data, 200);
        }

        $manager->singlePanel($form->createView());

        return $pageManager->createBreadcrumbs('Edit Notification Event',
            [
                ['uri' => 'system_admin__notification_events', 'name' => 'Notification Events'],
            ]
        )
            ->render(['containers' => $manager->getBuiltContainers()]);
    }

    /**
     * notificationListenerDelete
     * @param NotificationEvent $event
     * @param NotificationListener $listener
     * @param ContainerManager $manager
     * @Route("/notification/{event}/listener/{listener}/delete/", name="notification_listener_delete")
     * @Security("is_granted('ROLE_ROUTE', ['system_admin__notification_edit'])");
     * @return JsonResponse
     */
    public function notificationListenerDelete(NotificationEvent $event, NotificationListener $listener, ContainerManager $manager)
    {
        $data = [];
        $data['errors'] = [];
        $data['form'] = [];
        $em = $this->getDoctrine()->getManager();
        if (!$event->getListeners()->contains($listener)) {
            $data = ErrorMessageHelper::getInvalidInputsMessage($data, true);
            return JsonResponse::create($data, 200);
        }

        try {
            $event->removeListener($listener);
            $em->remove($listener);
            $em->flush();
        } catch (PDOException $e) {
            $data = ErrorMessageHelper::getDatabaseErrorMessage($data, true);
            return JsonResponse::create($data, 200);
        }
        $em->refresh($event);
        $form = $this->createForm(NotificationEventType::class, $event, ['action' => $this->generateUrl('system_admin__notification_edit', ['event' => $event->getId()]), 'listener_delete_route' => $this->generateUrl('system_admin__notification_listener_delete', ['listener' => '__id__', 'event' => '__event__'])]);

        $manager->singlePanel($form->createView());
        $data['form'] = $manager->getFormFromContainer();
        if ($data['errors'] === []) {
            $data = ErrorMessageHelper::getSuccessMessage($data, true);
        }

        //JSON Response required.
        return JsonResponse::create($data, 200);
    }
}