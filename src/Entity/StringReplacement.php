<?php
/**
 * Created by PhpStorm.
 *
 * Kookaburra
 *
 * (c) 2018 Craig Rayner <craig@craigrayner.com>
 *
 * UserProvider: craig
 * Date: 24/11/2018
 * Time: 16:16
 */
namespace Kookaburra\SystemAdmin\Entity;

use App\Manager\EntityInterface;
use App\Manager\Traits\BooleanList;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StringReplacement
 * @package Kookaburra\SystemAdmin\Entity
 * @ORM\Entity(repositoryClass="Kookaburra\SystemAdmin\Repository\StringReplacementRepository")
 * @ORM\Table(options={"auto_increment": 1}, name="String")
 */
class StringReplacement implements EntityInterface
{
    use BooleanList;
    /**
     * @return array
     */
    public static function getModeList(): array
    {
        return self::$modeList;
    }

    /**
     * @var integer|null
     * @ORM\Id
     * @ORM\Column(type="integer", columnDefinition="INT(8) UNSIGNED AUTO_INCREMENT")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return StringReplacement
     */
    public function setId(?int $id): StringReplacement
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=100)
     * @Assert\Length(max=100)
     * @Assert\NotBlank()
     */
    private $original;

    /**
     * @return string|null
     */
    public function getOriginal(): ?string
    {
        return $this->original;
    }

    /**
     * setOriginal
     *
     * @param string|null $original
     * @return StringOriginal
     */
    public function setOriginal(?string $original): StringReplacement
    {
        $this->original = mb_substr($original, 0, 100);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=100)
     * @Assert\Length(max=100)
     * @Assert\NotBlank()
     */
    private $replacement;

    /**
     * @return string|null
     */
    public function getReplacement(): ?string
    {
        return $this->replacement;
    }

    /**
     * setReplacement
     *
     * @param string|null $replacement
     * @return StringReplacement
     */
    public function setReplacement(?string $replacement): StringReplacement
    {
        $this->replacement = mb_substr($replacement, 0, 100);
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=8)
     * @Assert\Choice(callback="getModeList")
     */
    private $mode = 'Whole';

    /**
     * @var array
     */
    private static $modeList = [
        'Whole',
        'Partial',
    ];

    /**
     * @return string|null
     */
    public function getMode(): ?string
    {
        return $this->mode;
    }

    /**
     * setMode
     *
     * @param string|null $mode
     * @return StringReplacement
     */
    public function setMode(?string $mode): StringReplacement
    {
        $this->mode = in_array($mode, self::getModeList()) ? $mode : null ;
        return $this;
    }

    /**
     * @var string|null
     * @ORM\Column(length=1, name="caseSensitive")
     * @Assert\Choice(callback="getBooleanList")
     */
    private $caseSensitive = 'N';

    /**
     * @return bool
     */
    public function isCaseSensitive(): bool
    {
        return $this->getCaseSensitive() === 'Y' ? true : false ;
    }

    /**
     * @return string|null
     */
    public function getCaseSensitive(): string
    {
        return $this->caseSensitive = in_array($this->caseSensitive, self::getBooleanList()) ? $this->caseSensitive : 'N' ;
    }

    /**
     * setCaseSensitive
     *
     * @param string|null $caseSensitive
     * @return StringReplacement
     */
    public function setCaseSensitive(?string $caseSensitive): StringReplacement
    {
        $this->caseSensitive = in_array($caseSensitive, self::getBooleanList()) ? $caseSensitive : 'N' ;
        return $this;
    }

    /**
     * @var integer|null
     * @ORM\Column(type="smallint", columnDefinition="INT(2)", options={"default": "0"})
     * @Assert\Range(max="99", min="1")
     */
    private $priority;

    /**
     * @return int|null
     */
    public function getPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * Priority.
     *
     * @param int|null $priority
     * @return StringReplacement
     */
    public function setPriority(?int $priority): StringReplacement
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * toArray
     * @param string|null $name
     * @return array
     */
    public function toArray(?string $name = NULL): array
    {
        $result =  (array) $this;
        $x = [];
        foreach($result as $q=>$w){
            $x[str_replace("\x00Kookaburra\SystemAdmin\Entity\StringReplacement\x00", '', $q)] = $w;
        }
        return $x;
    }
}