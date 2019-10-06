<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 19/08/2019
 * Time: 18:10
 */

namespace Kookaburra\SystemAdmin\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class CurrentPassword
 * @package App\Validator
 */
class CurrentPassword extends Constraint
{
    /**
     * @var string
     */
    public $translationDomain = 'messages';

    /**
     * @var string
     */
    public $message = 'Your request failed due to incorrect current password.';

    /**
     * validatedBy
     * @return string
     */
    public function validatedBy()
    {
        return CurrentPasswordValidator::class;
    }
}