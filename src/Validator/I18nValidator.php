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
 * Date: 24/07/2019
 * Time: 10:59
 */

namespace Kookaburra\SystemAdmin\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class I18nValidator
 * @package Kookaburra\SystemAdmin\Validator
 */
class I18nValidator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        $valid = \Kookaburra\SystemAdmin\Entity\I18n::getLanguages();

        if (!isset($valid[$value]))
            $this->context->buildViolation('The language {code} selected is not a valid language choice for Kookaburra. Valid choices are {codes}')
                ->setParameter('{code}', $value)
                ->setParameter('{codes}', implode(', ', $valid))
                ->setTranslationDomain('kookaburra')
                ->addViolation();
    }
}