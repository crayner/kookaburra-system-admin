<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 19/08/2019
 * Time: 18:11
 */

namespace Kookaburra\SystemAdmin\Validator;

use Kookaburra\UserAdmin\Util\UserHelper;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class CurrentPasswordValidator
 * @package App\Validator
 */
class CurrentPasswordValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $user = UserHelper::getCurrentSecurityUser();
        if (!$user->isPasswordValid($value)) {
            $this->context->buildViolation($constraint->message)
                ->setTranslationDomain($constraint->translationDomain)
                ->addViolation();
        }
    }

}