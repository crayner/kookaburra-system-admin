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
 * Date: 22/09/2019
 * Time: 11:25
 */

namespace Kookaburra\SystemAdmin\Validator;

use Kookaburra\SystemAdmin\Form\Entity\ImportControl;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ImportStep2Validator
 * @package App\Validator
 */
class ImportStep2Validator extends ConstraintValidator
{
    /**
     * validate
     * @param mixed $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof ImportControl)
            $this->context->buildViolation($constraint->message)
                ->addViolation();

        if (in_array($value->getMode(), ['sync', 'update']) && ($value->isSyncField() && in_array($value->getSyncKey(),['',null])))
            $this->context->buildViolation($constraint->message)
                ->atPath('syncKey')
                ->addViolation();
    }

}