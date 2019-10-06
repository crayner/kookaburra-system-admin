<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 22/09/2019
 * Time: 11:24
 */

namespace Kookaburra\SystemAdmin\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class ImportStep2
 * @package App\Validator
 */
class ImportStep2 extends Constraint
{
    public $message = 'Your request failed because your inputs were invalid.';

    /**
     * getTargets
     * @return array|string
     */
    public function getTargets()
    {
        return parent::CLASS_CONSTRAINT;
    }
}