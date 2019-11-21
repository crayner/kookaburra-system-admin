<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/11/2018
 * Time: 15:27
 */
namespace Kookaburra\SystemAdmin\Voter;

use Kookaburra\UserAdmin\Util\SecurityHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class GibbonVoter
 * @package Kookaburra\SystemAdmin\Voter
 */
class GibbonVoter implements VoterInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RequestStack
     */
    private $stack;

    /**
     * GibbonVoter constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger, RequestStack $stack)
    {
        $this->logger = $logger;
        $this->stack = $stack;
    }
    /**
     * vote
     *
     * @param TokenInterface $token
     * @param mixed $subject
     * @param array $attributes
     * @return int
     * @throws \Exception
     */
    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        if (in_array('ROLE_ACTION', $attributes))
        {
            $subject = $this->resolveSubject($subject ?: []);
            if (SecurityHelper::isActionAccessible($subject[0], $subject[1]))
                return VoterInterface::ACCESS_GRANTED;
            else {
                if (empty($token->getUser()) || ! $token->getUser() instanceof UserInterface)
                    $this->logger->info(sprintf('The user was not correctly authenticated to authorise for action "%s".', $subject[0]), $subject);
                else
                    $this->logger->info(sprintf('The user "%s" attempted to access the action "%s" and was denied.', $token->getUser()->formatName(), $subject[0]), $subject);
                return VoterInterface::ACCESS_DENIED;
            }
        } elseif (in_array('ROLE_ROUTE', $attributes))
        {
            $subject[0] = !isset($subject[0]) ? $this->getRequest()->get('_route') : $subject[0];
            $subject = $this->resolveSubject($subject);
            if (SecurityHelper::isRouteAccessible($subject[0], $subject[1]))
                return VoterInterface::ACCESS_GRANTED;
            else {
                if (empty($token->getUser()) || ! $token->getUser() instanceof UserInterface)
                    $this->logger->info(sprintf('The user was not correctly authenticated to authorise for route "%s".', $subject[0]), $subject);
                else
                    $this->logger->info(sprintf('The user "%s" attempted to access the route "%s" and was denied.', $token->getUser()->formatName(), $subject[0]), $subject);
                return VoterInterface::ACCESS_DENIED;
            }
        } elseif (in_array('ROLE_HIGHEST', $attributes)) {
            $subject = $this->resolveHighestSubject($subject);
            $highest = SecurityHelper::getHighestGroupedAction($subject[0]);
            switch ($subject[2]) {
                case '===':
                    if ($highest = $subject[1])
                        return VoterInterface::ACCESS_GRANTED;
                    break;
                case '!==':
                    if ($highest != $subject[1])
                        return VoterInterface::ACCESS_GRANTED;
                    break;
                default:
                    dd($subject,$highest);
            }
            return VoterInterface::ACCESS_DENIED;
        }
        return VoterInterface::ACCESS_ABSTAIN;
    }

    /**
     * resolveSubject
     * @param array $subject
     * @return array
     */
    private function resolveSubject(array $subject): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            0 => 'You can never find this string in the action table.',
            1 => '%',
        ]);
        return $resolver->resolve($subject);
    }

    /**
     * resolveHighestSubject
     * @param array $subject
     * @return array
     */
    private function resolveHighestSubject(array $subject): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            0 => 'You can never find this string in the action table.',
            1 => 'do nothing',
            2 => '===',
        ]);
        $resolver->setAllowedValues('2', ['===','!==','>','<','>=','<=']);
        return $resolver->resolve($subject);
    }

    /**
     * @var Request
     */
    private $request;

    /**
     * getRequest
     * @return Request
     */
    private function getRequest(): Request
    {
        if (null === $this->request)
            $this->request = $this->stack->getCurrentRequest();
        return $this->request;
    }
}
