<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 11/09/2019
 * Time: 11:45
 */

namespace Kookaburra\SystemAdmin\Validator;

use App\Entity\Person;
use App\Entity\YearGroup;
use App\Provider\ProviderFactory;
use App\Util\UserHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class NotificationListenerValidator
 * @package App\Validator\SystemAdmin
 */
class NotificationListenerValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof \App\Entity\NotificationListener) {
            $this->context->buildViolation('Your request failed because your inputs were invalid.')
                ->atPath('person')
                ->setTranslationDomain('messages')
                ->addViolation();
            return;
        }

        if (!$value->getPerson() instanceof Person)
            $this->context->buildViolation('The value should not be empty.')
                ->atPath('person')
                ->setTranslationDomain('messages')
                ->addViolation();

        if ($value->getScopeType() !== 'All') {
            if (intval($value->getScopeID()) === 0) {
                $this->context->buildViolation('The value should not be empty.')
                    ->atPath('scopeID')
                    ->setTranslationDomain('messages')
                    ->addViolation();
                return ;
            }
        }
        if ($value->getScopeType() === 'gibbonPersonIDStudent') {
            $student = ProviderFactory::getRepository(Person::class)->find($value->getScopeID());
            if (!$student instanceof Person || !UserHelper::isStudent($student)) {
                $this->context->buildViolation('Not a valid student.')
                    ->atPath('scopeID')
                    ->setTranslationDomain('messages')
                    ->addViolation();
            }
        }
        if ($value->getScopeType() === 'gibbonPersonIDStaff') {
            $staff = ProviderFactory::getRepository(Person::class)->find($value->getScopeID());
            if (!$staff instanceof Person || !UserHelper::isStaff($staff)) {
                $this->context->buildViolation('Not a valid staff member.')
                    ->atPath('scopeID')
                    ->setTranslationDomain('messages')
                    ->addViolation();
            }
        }
        if ($value->getScopeType() === 'gibbonYearGroupID') {
            $staff = ProviderFactory::getRepository(YearGroup::class)->find($value->getScopeID());
            if (!$staff instanceof YearGroup) {
                $this->context->buildViolation('Not a valid Year Group.')
                    ->atPath('scopeID')
                    ->setTranslationDomain('messages')
                    ->addViolation();
            }
        }
    }

}