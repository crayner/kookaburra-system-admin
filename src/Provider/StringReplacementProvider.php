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
 * Date: 9/08/2019
 * Time: 13:21
 */

namespace Kookaburra\SystemAdmin\Provider;

use App\Provider\EntityProviderInterface;
use Kookaburra\SystemAdmin\Entity\StringReplacement;
use App\Manager\Traits\EntityTrait;

/**
 * Class StringReplacementProvider
 * @package Kookaburra\SystemAdmin\Provider
 */
class StringReplacementProvider implements EntityProviderInterface
{
    use EntityTrait;

    /**
     * @var string
     */
    private $entityName = StringReplacement::class;

    /**
     * getPaginationResults
     * @return array
     */
    public function getPaginationResults()
    {
        return $this->getRepository()->getPaginationSearch();
;    }
}