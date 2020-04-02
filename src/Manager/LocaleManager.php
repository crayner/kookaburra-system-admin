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
 * Date: 1/04/2020
 * Time: 12:45
 */

namespace Kookaburra\SystemAdmin\Manager;

use App\Provider\ProviderFactory;
use Gibbon\Locale;
use Kookaburra\SystemAdmin\Entity\I18n;

/**
 * Class LocaleManager
 * @package Kookaburra\SystemAdmin\Manager
 * @todo Remove extends Locale
 */
class LocaleManager extends Locale
{
    /**
     * @var I18n
     */
    private $localeEntity;

    /**
     * setLocale
     * @param string $i18nCode
     */
    public function setLocale(string $i18nCode = null)
    {
        $this->localeEntity = ProviderFactory::getRepository(I18n::class)->findOneByCode($i18nCode);
        if (empty($this->localeEntity))
            $this->localeEntity = ProviderFactory::getRepository(I18n::class)->findOneByCode('en_GB');

         if (empty($this->localeEntity))
            return;
        
        putenv('LC_ALL='.$this->localeEntity->getCode().'.utf8');
        putenv('LANG='.$this->localeEntity->getCode().'.utf8');
        putenv('LANGUAGE='.$this->localeEntity->getCode().'.utf8');
        $localeSet = setlocale(LC_ALL, $this->localeEntity->getCode().'.utf8',
            $this->localeEntity->getCode().'.UTF8',
            $this->localeEntity->getCode().'.utf-8',
            $this->localeEntity->getCode().'.UTF-8',
            $this->localeEntity->getCode());
    }
}