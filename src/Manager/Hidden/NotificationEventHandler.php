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
 * Date: 29/03/2020
 * Time: 08:37
 */

namespace Kookaburra\SystemAdmin\Manager\Hidden;

use App\Provider\ProviderFactory;
use App\Util\ErrorMessageHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Driver\PDOException;
use Kookaburra\SystemAdmin\Entity\NotificationEvent;
use Kookaburra\SystemAdmin\Entity\NotificationListener;
use Kookaburra\SystemAdmin\Form\NotificationEventType;
use Kookaburra\UserAdmin\Entity\Person;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;

/**
 * Class NotificationEventHandler
 * @package Kookaburra\SystemAdmin\Manager\Hidden
 */
class NotificationEventHandler
{
    /**
     * handleRequest
     * @param Request $request
     * @param Form $form
     * @param NotificationEvent $event
     * @return array
     */
    public function handleRequest(Request $request, Form $form, NotificationEvent $event): array
    {
        $content = json_decode($request->getContent(), true);
        $em = ProviderFactory::getEntityManager();
        $em->refresh($event);
        $event->getListeners();
        $validator = Validation::createValidator();
        $violations = new ConstraintViolationList();
        foreach($content['listeners'] as $q=>$w) {
            $w['id'] = array_key_exists('id',$w) ? $w['id'] : 0 ;
            $listener = ProviderFactory::getRepository(NotificationListener::class)->find(intval($w['id'])) ?: new NotificationListener();
            $person = ProviderFactory::getRepository(Person::class)->find(intval($w['person']));
            $listener->setPerson($person)
                ->setScopeType($w['scopeType'])
                ->setScopeID($w['scopeID'])
                ->setEvent($event);
            ;
            $violations->addAll($validator->validate($listener));
        }

        $people = [];
        $flush = false;
        foreach($event->getListeners() as $listener)
        {
            if ($listener->getScopeType() === 'All') {
                $personalListeners = $event->getListenersByPerson($listener);
                foreach ($personalListeners as $entity) {
                    $event->removeListener($entity);
                    if ($entity->getId() > 0) {
                        $em->remove($entity);
                        $flush = true;
                    }
                }
            }
        }
        if ($flush)
            $em->flush();

        $violations->addAll($validator->validate($event));

        if ($violations->count() === 0) {
            try {
                $em->persist($event);
                $em->flush();

                $data = ErrorMessageHelper::getSuccessMessage([], true);
            } catch (PDOException | \PDOException $e) {
                $data = ErrorMessageHelper::getDatabaseErrorMessage([], true);
            }
        } else {
            $data = ErrorMessageHelper::getInvalidInputsMessage([], true);
        }

        return $data;
    }
}