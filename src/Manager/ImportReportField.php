<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * User: craig
 * Date: 23/09/2019
 * Time: 13:20
 */

namespace Kookaburra\SystemAdmin\Manager;

use Kookaburra\SystemAdmin\Entity\Role;
use App\Entity\YearGroup;
use App\Provider\ProviderFactory;
use App\Util\TranslationsHelper;
use App\Validator\RoleList;
use App\Validator\YearGroupList;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;

/**
 * Class ImportReportField
 * @package App\Manager\Entity\SystemAdmin
 */
class ImportReportField
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $desc;

    /**
     * @var array
     */
    private $desc_params = [];

    /**
     * @var array
     */
    private $args;

    /**
     * @var array
     */
    private $relationship = [];

    /**
     * @var string
     */
    private $select;

    /**
     * @var ArrayCollection
     */
    private $relationalEntities;

    /**
     * @var ConstraintViolationList
     */
    private $violations;

    /**
     * ImportReportField constructor.
     */
    public function __construct(string $name, array $details)
    {
        $this->setName($name);
        $resolver = new OptionsResolver();
        $resolver->setRequired(['label', 'args', 'select']);
        $resolver->setDefaults(
            [
                'desc' => '',
                'relationship' => [],
                'descParams' => [],
            ]
        );
        $details = $resolver->resolve($details);

        foreach($details as $name=>$value)
        {
            $name = 'set' . ucfirst($name);
            $this->$name($value);
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Name.
     *
     * @param string $name
     * @return ImportReportField
     */
    public function setName(string $name): ImportReportField
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Label.
     *
     * @param string $label
     * @return ImportReportField
     */
    public function setLabel(string $label): ImportReportField
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getDesc(): string
    {
        return $this->desc;
    }

    /**
     * Desc.
     *
     * @param string $desc
     * @return ImportReportField
     */
    public function setDesc(string $desc): ImportReportField
    {
        $this->desc = $desc;
        return $this;
    }

    /**
     * getArg
     * @param string $name
     * @return mixed
     */
    public function getArg(string $name)
    {
        return $this->getArgs()[$name];
    }

    /**
     * setArg
     * @param string $name
     * @param $value
     * @return ImportReportField
     */
    private function setArg(string $name, $value): ImportReportField
    {
        $this->getArgs()[$name] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * Args.
     *
     * @param array $args
     * @return ImportReportField
     */
    public function setArgs(array $args): ImportReportField
    {
        $resolver = new OptionsResolver();

        $resolver-> setDefaults(
            [
                'filter' => 'string',
                'required' => false,
                'custom' => false,
                'linked' => false,
                'hidden' => false,
                'kind' => '',
                'length' => false,
                'scale' => null,
                'elements' => [],
                'desc' => '',
                "columnName" => '',
                "fieldName" => '',
                "nullable" => false,
                "precision" => null,
                "type" => 'string',
                "unique" => false,
                'columnDefinition' => '',
                'function' => false,
                'options' => [],
                'readonly' => false,
                'serialise' => false,
                'id' => false,
                'enum' => [],
            ]
        );
        $resolver->setAllowedValues('filter', ['string','numeric','schoolyear','html','yesno','yearlist',
            'date', 'language','country','integer','enum','url','array','year_group_list','time','datetime',
            'role_list','boolean']);

        $this->args = $resolver->resolve($args);
        return $this;
    }

    /**
     * @return bool|array
     */
    public function getRelationship()
    {
        return $this->relationship;
    }

    /**
     * Relationship.
     *
     * @param array $relationship
     * @return ImportReportField
     */
    public function setRelationship(array $relationship): ImportReportField
    {
        if ($relationship === []) {
            $this->relationship = $relationship;
            return $this;
        }

        $resolver = new OptionsResolver();
        $resolver->setRequired(['table', 'field']);
        $resolver->setDefault('key', 'id');

        $this->relationship = $resolver->resolve($relationship);
        return $this;
    }

    /**
     * @return string
     */
    public function getSelect(): string
    {
        return $this->select;
    }

    /**
     * Select.
     *
     * @param string $select
     * @return ImportReportField
     */
    public function setSelect(string $select): ImportReportField
    {
        $this->select = $select;
        return $this;
    }

    /**
     * Is Field Hidden
     *
     * @param string  Field name
     * @return  bool true if marked as a hidden field (or is linked)
     */
    public function isFieldHidden(): bool
    {
        if ($this->isFieldLinked()) {
            return true;
        }

        return $this->getArg('hidden');
    }

    /**
     * Is Field Linked to another field (for relational reference)
     *
     * @param string  Field name
     * @return  bool true if marked as a linked field
     */
    public function isFieldLinked(): bool
    {
        return $this->getArg('linked');
    }

    /**
     * isRequired
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->getArg('required');
    }

    /**
     * Create a human friendly representation of the field value type
     *
     * @param string  Field name
     * @return  array
     */
    public function readableFieldType(): array
    {
        $filter = $this->getArg('filter');
        $kind = $this->getArg('kind');
        $length = $this->getArg('length');

        if ($kind === '') {
            $this->setValueTypeByFilter();
            $kind = $this->getArg('kind');
            $length = $this->getArg('length');
        }

        if ($filter === 'string' && intval($length) > 0)
            $kind = 'char';

        if ($this->isRelational()) {
            extract($this->getRelationship());
            $field = is_array($field) ? current($field) : $field;

            return [
                'prompt' => 'Text',
                'title' => 'Each {name} value should match an existing {field} in table {table}.',
                'titleParams' => ['{name}' => TranslationsHelper::translate($this->getLabel()), '{field}' => $field, '{table}' => !empty($join) ? $join : $table,],
                'extra' => $field,
            ];
        }

        switch ($filter) {
            case 'email':
                return __('Email ({number} chars)', ['number' => $length]);
            case 'url':
                return [
                    'prompt' => 'URL (# chars)',
                    'promptParams' => ['count' => $length],
                ];
            case 'numeric':
                return ['prompt' =>'Number'];
            case 'integer':
                return ['prompt' =>'Integer'];
            case 'yesno':
                return [
                    'prompt' => 'Y or N',
                ];
            case 'html':
                return [
                    'prompt' => 'HTML Description',
                    'title' => 'Safe HTML usage is defined as System/AllowableHTML in the Settings.',
                ];
            case 'yearlist':
                return [
                    'prompt' => 'Year List',
                    'title' => 'Comma separated list of Year Group ID',
                ];
            case 'date':
                return [
                    'prompt' => 'Date (YYYY-MM-DD)',
                ];
            case 'time':
                return [
                    'prompt' => 'Time (HH:mm:ss)',
                ];
            case 'datetime':
                return [
                    'prompt' => 'Date Time (YYYY-MM-DD HH:mm:ss)',
                ];
            case 'language':
                return [
                    'prompt' => 'Valid Unicode Language',
                ];
            case 'country':
                return [
                    'prompt' => 'Country per ISO 3166',
                ];
            case 'schoolyear':
            case 'string':
                return [
                    'prompt' => 'Text ({length} chars)',
                    'promptParams' => ['count' => intval($this->getArg('length'))],
                ];
            case 'enum':
                return [
                    'prompt' => 'Choose One',
                ];
        }

        if ($kind === '')
            dump($filter,$this);
        return [
            'prompt' => $filter . ' filter not defined.',
        ];
    }

    /**
     * setValueTypeByFilter
     * @param $fieldName
     */
    protected function setValueTypeByFilter()
    {
        $type = '';
        $kind = '';

        switch ($this->getArg( 'filter')) {
            case 'string':
                $type = 'text';
                $kind = 'text';
                break;
            case 'date':
                $type = 'date';
                $kind = 'date';
                break;
            case 'url':
                $type = 'url';
                $kind = 'text';
                break;
            case 'email':
                $type = 'email';
                $kind = 'text';
                break;
        }

        $this->setArg('type', $type);
        $this->setArg('kind', $kind);
    }

    /**
     * Is Field Relational
     *
     * @param string  Field name
     * @return  bool true if marked as a required field
     */
    public function isRelational(): bool
    {
        return count($this->getRelationship()) > 0 ? true : false;
    }

    /**
     * isHidden
     * @return bool
     */
    public function isHidden(): bool
    {
        return (bool) $this->getArg('hidden');
    }

    /**
     * isFieldReadOnly
     * @return bool
     */
    public function isFieldReadOnly(): bool
    {
        return (bool) $this->getArg('readonly');
    }

    /**
     * getValue
     * @param $value
     * @param ArrayCollection $data
     * @return \DateTime|false|int|object|string|null
     * @throws \Exception
     */
    public function getValue($value, ArrayCollection $data)
    {
        if ($this->getArg('filter') === 'year_group_list') {
            $w = $this->reverseTransformYearGroups($value);
            $data->get($this->getLabel())->violations = $this->getViolations();
            $this->setViolations(null);
            return $w;
        }

        if ($this->getArg('filter') === 'role_list') {
            $w = $this->reverseTransformRoles($value);
            $data->get($this->getLabel())->violations = $this->getViolations();
            $this->setViolations(null);
            return $w;
        }

        if ($this->isRelational()) {
            extract($this->getRelationship());
            if (is_string($field))
                $field = [$field];
            $search = [];
            foreach($field as $q=>$name) {
                if ($q === 0) {
                    $search[$name] = $value;
                } else {
                    $subField = $data->filter(function(\stdClass $class) use ($name) {
                        return $class->field->getName() === $name;
                    })->first();
                    $search[$name] = $subField->value;
                }
            }
            $table = '\App\Entity\\'.$table;
            $entity = $this->getRelationalEntity($table, $field, $search) ?: ProviderFactory::getRepository($table)->findOneBy($search);
            $this->addRelationalEntity($table, $field, $search, $entity);
            return $entity;
        }
        switch ($this->getArg('filter')) {
            case 'yesno':
                $value = in_array(strtolower($value),  ['true','y','yes','1']) ? 'Y' : 'N';
                break;
            case 'date':
                if ($this->getArg('nullable') && ('' === $value || null === $value))
                    return null;
                $value = new \DateTime($value . ' 00:00:00');
                break;
            case 'time':
                if ($this->getArg('nullable') && ('' === $value || null === $value))
                    return null;
                $value = new \DateTime('1970-01-01 '.$value);
                break;
            case 'language':
                if ($this->getArg('nullable') && ('' === $value || null === $value))
                    return null;
                if (!in_array($value, ['',null])) {
                    $languages = Languages::getNames();
                    if (in_array($value, $languages)) {
                        $value = array_search($value, $languages);
                        break;
                    }
                    if (isset($languages[$value]))
                        break;
                }
                break;
            case 'country':
                if ($this->getArg('nullable') && ('' === $value || null === $value))
                    return null;
                if (!in_array($value, ['',null])) {
                    $countries = Countries::getNames();
                    if (in_array($value, $countries)) {
                        $value = array_search($value, $countries);
                        break;
                    }
                    if (isset($countries[$value]))
                        break;
                }
                break;
            case 'integer':
                if ($this->getArg('nullable') && ('' === $value || null === $value))
                    return null;
                $value = intval($value);
                break;
            case 'enum':
            case 'url':
            case 'string':
            case 'numeric':
            case 'schoolyear':
            case 'html':
                if ($this->getArg('nullable') && ('' === $value || null === $value)) {
                    return null;
                }
                break;
            case 'array':
                if (is_array($value))
                    break;
                dd($this,$value);
                break;
            case 'simple_array':
                if (is_array($value))
                    break;
                dd($this,$value);
                break;
            default:
                dd($this->getArg('filter'), $value, $this);
        }
        return $value;
    }

    /**
     * getRelationalEntities
     * @return ArrayCollection
     */
    public function getRelationalEntities(): ArrayCollection
    {
        return $this->relationalEntities = $this->relationalEntities ?: new ArrayCollection();
    }

    /**
     * RelationalEntities.
     *
     * @param ArrayCollection $relationalEntities
     * @return ImportReportField
     */
    public function setRelationalEntities(ArrayCollection $relationalEntities): ImportReportField
    {
        $this->relationalEntities = $relationalEntities;
        return $this;
    }

    /**
     * addRelationalEntity
     * @param string $table
     * @param string $field
     * @param string $value
     * @param $entity
     * @return ImportReportField
     */
    public function addRelationalEntity(string $table, $field, array $value, $entity): ImportReportField
    {
        $field = is_string($field) ? $field : implode('_',$field);
        $value = is_string($value) ? $value : implode('_',$value);
        $fieldCollection = $this->getRelationalEntities()->get($table) ?: new ArrayCollection();
        $valueCollection = $fieldCollection->get($field) ?: new ArrayCollection();
        $valueCollection->set($value, $entity);
        $fieldCollection->set($field, $valueCollection);
        $this->getRelationalEntities()->set($table, $fieldCollection);
        return $this;
    }

    /**
     * Relational.
     *
     * @param ArrayCollection $relational
     * @return ImportReportField
     */
    public function getRelationalEntity(string $table, $field, array $value)
    {
        $field = is_string($field) ? $field : implode('_',$field);
        $value = is_string($value) ? $value : implode('_',$value);
        $fieldCollection = $this->getRelationalEntities()->get($table) ?: new ArrayCollection();
        $valueCollection = $fieldCollection->get($field) ?: new ArrayCollection();
        return $valueCollection->get($value);
    }

    /**
     * @return array
     */
    public function getDescParams(): array
    {
        return $this->desc_params;
    }

    /**
     * DescParams.
     *
     * @param array $desc_params
     * @return ImportReportField
     */
    public function setDescParams(array $desc_params): ImportReportField
    {
        $this->desc_params = $desc_params;
        return $this;
    }

    /**
     * transformYearGroups
     * @param $name
     * @param $value
     * @return array
     */
    public function transformYearGroups(array $value): array
    {
        extract($this->getRelationship());
        $yearGroups = ProviderFactory::getRepository(YearGroup::class)->findByYearGroupIDList($value, $field);

        return array_keys($yearGroups);
    }

    /**
     * reverseTransformYearGroups
     * @param $name
     * @param $value
     * @return array
     */
    public function reverseTransformYearGroups($value): array
    {
        extract($this->getRelationship());
        $value = explode(',', $value);
        $yearGroups = ProviderFactory::getRepository(YearGroup::class)->findByYearGroupList($value, $field);

        if (count($value) !== count($yearGroups))
        {
            // Validation Violation
            $validator = Validation::createValidator();
            $errors = $validator->validate($value, [new YearGroupList(['fieldName' => $field, 'message' => '{value} does not give a valid Year Group.', 'propertyPath' => $this->getName()])]);
            if ($errors->count() > 0)
                $this->getViolations()->addAll($errors);
        }
        return array_keys($yearGroups);
    }

    /**
     * getViolations
     * @return ConstraintViolationList
     */
    public function getViolations(): ConstraintViolationList
    {
        return $this->violations = $this->violations ?: new ConstraintViolationList();
    }

    /**
     * Violations.
     *
     * @param null|ConstraintViolationList $violations
     * @return ImportReportField
     */
    public function setViolations(?ConstraintViolationList $violations): ImportReportField
    {
        $this->violations = $violations;
        return $this;
    }

    /**
     * transformYearGroups
     * @param $name
     * @param $value
     * @return array
     */
    public function transformRoles(array $value): array
    {
        extract($this->getRelationship());
        $yearGroups = ProviderFactory::getRepository(Role::class)->findByRoleIDList($value, $field);

        return array_keys($yearGroups);
    }

    /**
     * reverseTransformYearGroups
     * @param $name
     * @param $value
     * @return array
     */
    public function reverseTransformRoles($value): array
    {
        extract($this->getRelationship());
        $value = explode(',', $value);
        $yearGroups = ProviderFactory::getRepository(Role::class)->findByRoleList($value, $field);

        if (count($value) !== count($yearGroups))
        {
            // Validation Violation
            $validator = Validation::createValidator();
            $errors = $validator->validate($value, [new RoleList(['fieldName' => $field, 'message' => '{value} does not give a valid Role.', 'propertyPath' => $this->getName()])]);
            if ($errors->count() > 0)
                $this->getViolations()->addAll($errors);
        }
        return array_keys($yearGroups);
    }
}