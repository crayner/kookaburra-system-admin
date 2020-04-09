<?php
/**
 * Created by PhpStorm.
 *
* Kookaburra
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * UserProvider: craig
 * Date: 23/11/2018
 * Time: 11:56
 */
namespace Kookaburra\SystemAdmin\Entity;

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use App\Util\TranslationsHelper;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class I18n
 * @package Kookaburra\SystemAdmin\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\SystemAdmin\Repository\I18nRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="i18n")
 */
class I18n implements EntityInterface
{
    use BooleanList;

    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="smallint", columnDefinition="INT(4) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(length=5)
     * @Assert\Choice(callback="getLanguages")
     */
    private $code;

    /**
     * @var string|null
     * @ORM\Column(length=100)
     */
    private $name;

    /**
     * @var string|null
     * @ORM\Column(length=10, nullable=true)
     */
    private $version;

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "Y"})
     */
    private $active = 'Y';

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "N"})
     */
    private $installed = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=1, name="systemDefault", options={"default": "N"})
     */
    private $systemDefault = 'N';

    /**
     * @var string|null
     * @ORM\Column(length=20, name="dateFormat")
     */
    private $dateFormat;

    /**
     * @var string|null
     * @ORM\Column(type="text", name="dateFormatRegEx")
     */
    private $dateFormatRegEx;

    /**
     * @var string|null
     * @ORM\Column(length=20, name="dateFormatPHP")
     */
    private $dateFormatPHP;

    /**
     * @var string|null
     * @ORM\Column(length=1, options={"default": "N"})
     */
    private $rtl = 'N';

    /**
     * @var bool
     */
    private $defaultLanguage = false;

    /**
     * @var array
     */
    private static $languages = array(
        'nl_NL' => 'Dutch - Nederland',
        'en_GB' => 'English - United Kingdom',
        'en_US' => 'English - United States',
        'es_ES' => 'Español',
        'fr_FR' => 'Français - France',
        'he_IL' => 'עברית - ישראל',
        'hr_HR' => 'Hrvatski - Hrvatska',
        'it_IT' => 'Italiano - Italia',
        'pl_PL' => 'Język polski - Polska',
        'pt_BR' => 'Português - Brasil',
        'ro_RO' => 'Română',
        'sq_AL' => 'Shqip - Shqipëri',
        'vi_VN' => 'Tiếng Việt - Việt Nam',
        'tr_TR' => 'Türkçe - Türkiye',
        'ar_SA' => 'العربية - المملكة العربية السعودية',
        'th_TH' => 'ภาษาไทย - ราชอาณาจักรไทย',
        'ur_PK' => 'پاکستان - اُردُو',
        'zh_CN' => '汉语 - 中国',
        'zh_HK' => '體字 - 香港',
    );

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return I18n
     */
    public function setId(?int $id): I18n
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     * @return I18n
     */
    public function setCode(?string $code): I18n
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return I18n
     */
    public function setName(?string $name): I18n
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * @param string|null $version
     * @return I18n
     */
    public function setVersion(?string $version): I18n
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isActive(): bool
    {
        return $this->getActive() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getActive(): ?string
    {
        return self::checkBoolean($this->active);
    }

    /**
     * @param string|null $active
     * @return I18n
     */
    public function setActive(?string $active): I18n
    {
        $this->active = self::checkBoolean($active, 'Y');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInstalled(): ?string
    {
        $this->installed = (false === realpath(__DIR__ . '/../../../../../translations/messages+intl-icu.'.$this->getCode().'.yaml') ? 'N' : 'Y');

        return $this->installed;
    }

    /**
     * @return boolean
     */
    public function isInstalled(): bool
    {
        return $this->getInstalled() === 'Y';
    }

    /**
     * @param string|null $installed
     * @return I18n
     */
    public function setInstalled(?string $installed): I18n
    {
        $this->installed = self::checkBoolean($installed, 'N');
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSystemDefault(): bool
    {
        return $this->getSystemDefault() === 'Y' ? true : false;
    }

    /**
     * @return string|null
     */
    public function getSystemDefault(): ?string
    {
        return $this->systemDefault;
    }

    /**
     * @param string|null $systemDefault
     * @return I18n
     */
    public function setSystemDefault(?string $systemDefault): I18n
    {
        $this->systemDefault = self::checkBoolean($systemDefault, 'N');
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDateFormat(): ?string
    {
        return $this->dateFormat;
    }

    /**
     * @param string|null $dateFormat
     * @return I18n
     */
    public function setDateFormat(?string $dateFormat): I18n
    {
        $this->dateFormat = $dateFormat;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDateFormatRegEx(): ?string
    {
        return $this->dateFormatRegEx;
    }

    /**
     * @param string|null $dateFormatRegEx
     * @return I18n
     */
    public function setDateFormatRegEx(?string $dateFormatRegEx): I18n
    {
        $this->dateFormatRegEx = $dateFormatRegEx;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDateFormatPHP(): ?string
    {
        return $this->dateFormatPHP;
    }

    /**
     * @param string|null $dateFormatPHP
     * @return I18n
     */
    public function setDateFormatPHP(?string $dateFormatPHP): I18n
    {
        $this->dateFormatPHP = $dateFormatPHP;
        return $this;
    }

    /**
     * @return boolean|null
     */
    public function isRtl(): ?bool
    {
        return $this->getRtl() === 'Y';
    }

    /**
     * @return string|null
     */
    public function getRtl(): ?string
    {
        return self::checkBoolean($this->rtl, 'N');
    }

    /**
     * @param string|null $rtl
     * @return I18n
     */
    public function setRtl(?string $rtl): I18n
    {
        $this->rtl = self::checkBoolean($rtl, 'N');
        return $this;
    }

    /**
     * __toArray
     * @return array
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function __toArray(): array
    {
        $normaliser = new ObjectNormalizer();

        return $normaliser->normalize($this);
    }

    /**
     * @return array
     */
    public static function getLanguages(): array
    {
        return array_flip(self::$languages);
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = NULL): array {
        return [
            "id" => $this->getId(),
            "code" => $this->getCode(),
            "name" => $this->getName(),
            'active' => TranslationsHelper::translate($this->isActive() ? 'Yes' : 'No', [], 'messages'),
            'status' => $this->getStatus(),
            'isActive' => $this->isActive(),
            'isNotDefault' => !$this->isSystemDefault() && $this->isInstalled(),
        ];
    }

    /**
     * getStatus
     * @return string
     */
    public function getStatus(): string
    {
        $result = '';
        if ($this->isSystemDefault())
            $result .= ', ' . TranslationsHelper::translate('Default');

        if ($this->isInstalled())
            $result .= ', '.TranslationsHelper::translate('Installed');

        $result = trim($result,', ');

        return $result;
    }

    /**
     * @return bool
     */
    public function isDefaultLanguage(): bool
    {
        return $this->defaultLanguage;
    }

    /**
     * DefaultLanguage.
     *
     * @param bool $defaultLanguage
     * @return I18n
     */
    public function setDefaultLanguage(bool $defaultLanguage): I18n
    {
        $this->defaultLanguage = $defaultLanguage;
        return $this;
    }
}