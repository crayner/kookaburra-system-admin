<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/09/2019
 * Time: 12:19
 */

namespace Kookaburra\SystemAdmin\Manager;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ImportReportSecurity
 * @package App\Manager\Entity\SystemAdmin
 */
class ImportReportSecurity
{
    /**
     * @var string
     */
    private $module;

    /**
     * @var string
     */
    private $action;

    /**
     * @var boolean
     */
    private $protected;

    /**
     * @var string
     */
    private $entryUrl = '';

    /**
     * ImportReportSecurity constructor.
     */
    public function __construct(array $security)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(['module' => '', 'action' => '']);
        $security = $resolver->resolve($security);
        foreach($security as $name=>$value)
        {
            $name = 'set' . ucfirst($name);
            $this->$name($value);
        }
    }

    /**
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * Module.
     *
     * @param string $module
     * @return ImportReportSecurity
     */
    public function setModule(string $module): ImportReportSecurity
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Action.
     *
     * @param string $action
     * @return ImportReportSecurity
     */
    public function setAction(string $action): ImportReportSecurity
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return bool
     */
    public function isProtected(): bool
    {
        if (null ===  $this->protected) {
            if ('' === $this->getModule() || '' === $this->getAction())
                $this->protected = false;
            else
                $this->protected = true;
        }
        return $this->protected;
    }

    /**
     * Protected.
     *
     * @param bool $protected
     * @return ImportReportSecurity
     */
    public function setProtected(bool $protected): ImportReportSecurity
    {
        $this->protected = $protected;
        return $this;
    }

    /**
     * @return string
     */
    public function getEntryUrl(): string
    {
        return $this->entryUrl;
    }

    /**
     * EntryUrl.
     *
     * @param string $entryUrl
     * @return ImportReportSecurity
     */
    public function setEntryUrl(string $entryUrl): ImportReportSecurity
    {
        $this->entryUrl = $entryUrl;
        return $this;
    }
}