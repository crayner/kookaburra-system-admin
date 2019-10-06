<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 16/09/2019
 * Time: 12:21
 */

namespace Kookaburra\SystemAdmin\Manager;

use App\Entity\Action;
use App\Entity\Module;
use App\Exception\Exception;
use App\Exception\MissingClassException;
use App\Provider\ProviderFactory;
use Kookaburra\UserAdmin\Util\SecurityHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ImportReport
 * @package App\Manager\Entity
 */
class ImportReport
{
    /**
     * @var ImportReportDetails
     */
    private $details;

    /**
     * @var ImportReportSecurity
     */
    private $security;

    /**
     * @var Collection
     */
    private $join;

    /**
     * @var Collection
     */
    private $fields;

    /**
     * @var array
     */
    private $tablesUsed;

    /**
     * @var bool
     */
    private $usesDates = false;

    /**
     * @var array
     */
    private $uniqueKeys = [];

    /**
     * @var string
     */
    private $primaryKey = 'id';

    /**
     * @var array
     */
    private $fixedData = [];

    /**
     * @var array
     */
    private $orderBy = [];

    /**
     * ImportReport constructor.
     * @param $file
     * @throws \Exception
     */
    public function __construct($file, bool $matchDatabase = false)
    {
        $fileData = Yaml::parse(file_get_contents($file->getRealPath()));
        $resolver = new OptionsResolver();
        $resolver->setRequired(["details", "security", "fields"]);
        $resolver->setDefaults(["join" => [], 'uniqueKeys' => [], 'primaryKey' => 'id', 'fixedData' => [], 'orderBy' => []]);
        $fileData = $resolver->resolve($fileData);

        $this->setDetails($fileData['details']);
        foreach($fileData as $name=>$value)
        {
            $name = 'set' . ucfirst($name);
            $this->$name($value);
        }

        $join = new ImportReportJoin($this->getDetail('table'), ['table' => $this->getDetail('table'), 'alias' => $this->getDetail('alias'), 'primary' => true]);
        $this->addJoin($this->getDetail('table'), $join);

        $basename = str_replace('.'.$file->getExtension(), '', $file->getBasename());
        if ($basename !== $this->getDetails()->getName())
            throw new \Exception('The report name "'.$this->getDetails()->getName().'"" does not match the filename of the report.');

        $this->getDetails()->setGrouping($this->getSecurity()->getModule() ?: 'General');

        if ($matchDatabase) {
            $this->getDatabaseMetaData();
        }
    }

    /**
     * getDetails
     * @return ImportReportDetails
     */
    public function getDetails(): ImportReportDetails
    {
        return $this->details;
    }

    /**
     * setDetails
     * @param $details
     * @return ImportReport
     * @throws MissingClassException
     */
    public function setDetails($details): ImportReport
    {
        if (is_array($details))
            $details = new ImportReportDetails($details);
        if (!$details instanceof ImportReportDetails)
            throw new MissingClassException(sprintf('Report details must be loaded into a %s', ImportReportDetails::class));
        $this->details = $details;

        return $this;
    }

    /**
     * getSecurity
     * @return ImportReportSecurity
     */
    public function getSecurity(): ImportReportSecurity
    {
        return $this->security;
    }

    /**
     * setSecurity
     * @param $security
     * @return ImportReport
     * @throws MissingClassException
     */
    public function setSecurity($security): ImportReport
    {
        if (is_array($security))
            $security = new ImportReportSecurity($security);
        if (!$security instanceof ImportReportSecurity)
            throw new Exception(sprintf('Report security must be loaded into a %s', ImportReportSecurity::class));
        $this->security = $security;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getJoin(): Collection
    {
        return $this->join = $this->join ?: new ArrayCollection();
    }

    /**
     * Join.
     *
     * @param Collection $join
     * @return ImportReport
     */
    public function setJoin($join): ImportReport
    {
        if (is_array($join))
        {
            foreach($join as $name=>$details)
            {
                $w = new ImportReportJoin($name, $details);
                $this->addJoin($name, $w);
            }
            if (count($join) === $this->getJoin()->count() && null !== $this->getDetails()) {
                $master = new ImportReportJoin($this->getDetail('table'), ['table' => $this->getDetail('table'), 'alias' => $this->getDetail('alias'), 'primary' => true]);
                $this->addJoin($this->getDetail('table'), $master);
            }
        } else
            $this->join = $join;

        return $this;
    }

    /**
     * addJoin
     * @param string $name
     * @param ImportReportJoin $join
     * @return ImportReport
     * @throws Exception
     */
    public function addJoin(string $name, ImportReportJoin $join): ImportReport
    {
        if ($this->getJoin()->containsKey($name))
            return $this;
        $this->join->set($name, $join);

        return $this;
    }

    /**
     * @return Collection
     */
    public function getFields(): Collection
    {
        return $this->fields = $this->fields ?: new ArrayCollection();
    }

    /**
     * Fields.
     *
     * @param Collection $fields
     * @return ImportReport
     */
    public function setFields($fields): ImportReport
    {
        if (is_array($fields))
        {
            foreach($fields as $name=>$field)
            {
                if (isset($field['args']['filter']) && $field['args']['filter'] === 'enum')
                {
                    $table = $this->getTableFromSelect($field['select']);
                    if (isset($field['args']['enum'])) {
                        $resolver = new OptionsResolver();
                        $resolver->setRequired(['class','method']);
                        $enum = $resolver->resolve($field['args']['enum']);
                        $class = $enum['class'];
                        $method = $enum['method'];
                        $list = $class::$method();
                    } else {
                        $class = '\App\Entity\\'.$table->getTargetTable();
                        $method = 'get' . ucfirst(explode('.',$field['select'])[1]).'List';
                        $list = $class::$method();
                    }
                    $field['descParams'] = ['{list}' => implode('","', $list)];
                }
                $field = new ImportReportField($name, $field);
                $this->addField($name, $field);
            }
        } else
            $this->fields = $fields;

        return $this;
    }

    /**
     * addField
     * @param string $name
     * @param ImportReportField $field
     * @return ImportReport
     */
    private function addField(string $name, ImportReportField $field): ImportReport
    {
        if ($this->getFields()->contains($field))
            return $this;
        $this->fields->set($name, $field);
        if (! $this->isUsesDates() && in_array($field->getArg('filter'), ['date','datetime','time']))
            $this->usesDates = true;
        return $this;
    }

    /**
     * getTableFromSelect
     * @param string $select
     * @return ImportReportJoin
     */
    public function getTableFromSelect(string $select): ImportReportJoin
    {
        $alias = explode('.',$select)[0];
        return $this->getJoin()->get($this->getJoinAlias($alias));
    }

    /**
     * getDetail
     * @param string $name
     * @return mixed
     */
    public function getDetail(string $name)
    {
        $name = 'get' . ucfirst($name);
        return $this->getDetails()->$name();
    }

    /**
     * Load Access Data - for user permission checking, and category names
     *
     */
    public function loadAccessData()
    {
        if ($this->getSecurity()->isProtected()) {
            $module = $this->getModuleByName($this->getSecurity()->getModule());
            $action = ProviderFactory::getRepository(Action::class)->findOneByNameModule($this->getSecurity()->getAction(), $module);

            $this->getSecurity()->setEntryURL($action->getEntryURL());

            if ($this->getDetails()->getCategory() === 'Kookaburra')
                $this->getDetails()->setCategory($action->getCategory());
        }
    }

    /**
     * @var ArrayCollection
     */
    private $modules;

    /**
     * @return ArrayCollection
     */
    public function getModules(): ArrayCollection
    {
        if (null === $this->modules) {
            $this->modules = new ArrayCollection();
            foreach(ProviderFactory::getRepository(Module::class)->findAll() as $module)
                $this->modules->set($module->getName(), $module);
        }
        return $this->modules;
    }

    /**
     * getModuleByName
     * @param string $name
     */
    public function getModuleByName(string $name): Module
    {
        if ($this->getModules()->containsKey($name))
            return $this->getModules()->get($name);
        $this->modules->set($name, ProviderFactory::getRepository(Module::class)->findOneByName($name));
        return $this->modules->get($name);
    }

    /**
     * isImportAccessible
     * @return bool
     * @throws \Exception
     */
    public function isImportAccessible(): bool
    {
        if (!$this->getSecurity()->isProtected())
            return true;

        if (strpos($this->getSecurity()->getEntryUrl(), '.php') === false)
            return SecurityHelper::isRouteAccessible(strtolower(str_replace(' ', '_', $this->getSecurity()->getModule()) . '__' . $this->getSecurity()->getEntryUrl()));
        else
            return SecurityHelper::isActionAccessible('/modules/' . $this->getSecurity()->getModule() . '/' . $this->getSecurity()->getEntryUrl());
    }

    /**
     * getJoinAlias
     *
     * Returns the alias if the tableName or reference is given
     * Returns the target Table name if the alias is provided.
     * @param string $name
     * @return string
     * @throws \Exception
     */
    public function getJoinAlias(string $name): string
    {
        if ($this->getJoin()->containsKey($name)) {
            $join = $this->getJoin()->get($name)->getAlias();
        }

        $join = $this->getJoin()->filter(function (ImportReportJoin $details) use ($name) {
            return $details->getReference() === $name;
        });

        if ($join->count() === 1)
            return $join->first()->getAlias();

        $join = $this->getJoin()->filter(function (ImportReportJoin $details) use ($name) {
            return $details->getTargetTable('tableName') === $name;
        });

        if ($join->count() === 1)
            return $join->first()->getAlias();

        if ($join->count() > 1) {
            return $this->getJoinAlias(lcfirst($name));
        }

        $join = $this->getJoin()->filter(function (ImportReportJoin $details) use ($name) {
            return $details->getAlias() === $name;
        });

        if ($join->count() === 1) {
            return $join->first()->getTargetTable();

        }

        dump($name, $this, $this->getJoin());
        throw new \Exception('That will never work.  No alias found for ' . $name);
    }

    /**
     * @return array
     */
    public function getTablesUsed(): array
    {
        if (null === $this->tablesUsed)
        {
            $this->tablesUsed = [];
            foreach($this->getJoin() as $item)
                if (!in_array($item->getTargetTable(), $this->tablesUsed))
                    $this->tablesUsed[] = $item->getTargetTable();
        }
        return $this->tablesUsed;
    }

    /**
     * getFieldFilter
     * @param string $name
     * @return string
     */
    public function getFieldFilter(string $name): string
    {
        $field = $this->getFields()->get($name);
        return $field->getArg('filter');
    }

    /**
     * @return bool
     */
    public function isUsesDates(): bool
    {
        return $this->usesDates;
    }

    /**
     * getDatabaseMetadata
     * @throws \Exception
     */
    private function getDatabaseMetadata()
    {
        $em = ProviderFactory::getEntityManager();
        foreach($this->getFields() as $name=>$field) {
            $select = explode('.', $field->getSelect());
            $table = $this->getJoinAlias($select[0]);
            $metaData = $em->getClassMetadata('\App\Entity\\' . $table);
            if ($mapping = $metaData->getFieldMapping($select[1])) {
                $field->setArgs(array_merge($field->getArgs(), $mapping));
            }
        }
    }

    /**
     * getUniqueKey
     * @param string $name
     * @return array|null
     */
    public function getUniqueKey(string $name): ?array
    {
        return $this->isUniqueKey($name) ? $this->getUniqueKeys()[$name] : null;
    }

    /**
     * getUniqueKeys
     * @return array
     */
    public function getUniqueKeys(): array
    {
        return $this->uniqueKeys = $this->uniqueKeys ?: [];
    }

    /**
     * isUniqueKey
     * @param string $name
     * @return bool
     */
    public function isUniqueKey(string $name): bool
    {
        return isset($this->getUniqueKeys()[$name]);
    }

    /**
     * UniqueKeys.
     *
     * @param array $uniqueKeys
     * @return ImportReport
     */
    public function setUniqueKeys(array $uniqueKeys): ImportReport
    {
        foreach($uniqueKeys as $q=>$w){
            $resolver = new OptionsResolver();
            $resolver->setRequired([
                'label',
            ]);
            $resolver->setDefaults([
                'name' => $q,
                'fields' => [$q],
            ]);
            $resolver->setAllowedTypes('fields', ['array']);
            $uniqueKeys[$q] = $resolver->resolve($w);
        }

        $this->uniqueKeys = $uniqueKeys;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * PrimaryKey.
     *
     * @param string $primaryKey
     * @return ImportReport
     */
    public function setPrimaryKey(string $primaryKey): ImportReport
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    /**
     * findFieldByArg
     * @param string $argName
     * @param string $value
     * @return ImportReportField|null
     */
    public function findFieldByArg(string $argName, string $value): ?ImportReportField
    {
        $field = $this->getFields()->filter(function(ImportReportField $field) use ($argName, $value) {
            return $field->getArg($argName) === $value;
        });
        return $field->first() ?: null;
    }

    /**
     * getField
     * @param string $fieldName
     * @return ImportReportField|null
     */
    public function getField(string $fieldName): ?ImportReportField
    {
        return $this->getFields()->get($fieldName) ?: null;
    }

    /**
     * findFieldByLabel
     * @param string $label
     * @return ImportReportField|null
     */
    public function findFieldByLabel(string $label): ?ImportReportField
    {
        $field = $this->getFields()->filter(function(ImportReportField $field) use ($label) {
            return $field->getLabel() === $label;
        });
        return $field->first() ?: null;
    }

    /**
     * @return array
     */
    public function getFixedData(): array
    {
        return $this->fixedData;
    }

    /**
     * FixedData.
     *
     * @param array $fixedData
     * @return ImportReport
     */
    public function setFixedData(array $fixedData): ImportReport
    {
        $this->fixedData = $fixedData;
        return $this;
    }

    /**
     * isHiddenField
     * @param string $name
     * @return bool
     */
    public function isHiddenField(string $name): bool
    {
        $field = $this->getFields()->get($name);
        return $field->getArg('hidden');
    }

    /**
     * parseData
     *
     * Take serialised data and add to result.
     * @param array $row
     * @return array
     */
    public function parseData(array $row): array
    {
        foreach($this->getFields() as $name=>$field)
        {
            if (is_string($field->getArg('serialise')))
            {
                $source = $field->getArg('serialise');
                $fieldName = $field->getLabel();
                $row[$name] = $row[$source][$fieldName];
            }
        }

        return $row;
    }

    /**
     * @return array
     */
    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    /**
     * OrderBy.
     *
     * @param array $orderBy
     * @return ImportReport
     */
    public function setOrderBy(array $orderBy): ImportReport
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    public function getFieldByLabel(string $label): ImportReportField
    {
        if ($this->getFields()->containsKey($label))
            return $this->getFields()->get($label);

        return $this->getFields()->filter(function (ImportReportField $field) use ($label) {
            return $field->getLabel() === $label;
        })->first();
    }
}