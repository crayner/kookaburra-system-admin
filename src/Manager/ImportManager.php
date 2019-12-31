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
 * Date: 16/09/2019
 * Time: 12:14
 */

namespace Kookaburra\SystemAdmin\Manager;

use App\Entity\Setting;
use App\Form\Type\ToggleType;
use App\Provider\ProviderFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Kookaburra\SystemAdmin\Form\Entity\ImportColumn;
use Kookaburra\SystemAdmin\Form\Entity\ImportControl;
use Kookaburra\SystemAdmin\Form\ImportStepColumnType;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class ImportManager
 * @package App\Manager\SystemAdmin
 */
class ImportManager
{
    /**
     * @var ArrayCollection
     */
    private $importReports;

    /**
     * @var bool 
     */
    private $dataExport = false;

    /**
     * @var bool 
     */
    private $dataExportAll = false;

    /**
     * @var Importer
     */
    private $importer;

    /**
     * ImportManager constructor.
     * @param Importer $importer
     */
    public function __construct(Importer $importer)
    {
        $this->importer = $importer;
    }

    /**
     * Loads all YAML files from a folder and creates an ImportReport object for each
     *
     * @param bool $validateStructure
     * @return ArrayCollection
     * @throws \Exception
     */
    public function loadImportReportList(bool $validateStructure = false): ArrayCollection
    {
        $finder = new Finder();
        // Get the built-in import definitions
        $defaultFiles = $finder->files()->in($this->getImportReportDir())->name(['*.yaml', '*.yml']);

        // Create ImportReport objects for each file
        if ($finder->hasResults()) {
            foreach ($defaultFiles as $file) {
                $report = new ImportReport($file);
                $this->addImportReport($report);
            }
        }

        if (! is_dir($this->getCustomImportReportDir()))
            mkdir($this->getCustomImportReportDir(), 0755, true) ;

        $finder = new Finder();
        // Get the user-defined custom definitions
        $customFiles = $finder->files()->in($this->getCustomImportReportDir())->name(["*.yaml", '*.yml']);

        if ($finder->hasResults()) {
            foreach ($customFiles as $file) {
                $report = new ImportReport($file);
                $this->addImportReport($report);
            }
        }

        $this->sortImportReports();

        return $this->getImportReports();
    }

    /**
     * getImportReportDir
     * @return string
     */
    public function getImportReportDir()
    {
        return realpath(__DIR__ . "/../Resources/imports");
    }

    /**
     * getImportReports
     * @return ArrayCollection
     */
    public function getImportReports(): ArrayCollection
    {
        return $this->importReports = $this->importReports ?: new ArrayCollection();
    }

    /**
     * ImportReports.
     *
     * @param ArrayCollection $importReports
     * @return ImportManager
     */
    public function setImportReports(ArrayCollection $importReports): ImportManager
    {
        $this->importReports = $importReports;
        return $this;
    }

    /**
     * addImportReport
     * @param ImportReport $report
     * @return ImportManager
     */
    public function addImportReport(ImportReport $report): ImportManager
    {
        $this->getImportReports()->add($report);

        $report->loadAccessData();

        return $this;
    }

    /**
     * getCustomImportReportDir
     * @return string
     */
    public function getCustomImportReportDir()
    {
        $customFolder = ProviderFactory::create(Setting::class)->getSettingByScopeAsString('Data Admin', 'importCustomFolderLocation');

        return realpath(__DIR__ . '/../../../../../public/uploads/').trim($customFolder, '/ ');
    }

    /**
     * sortImportReports
     * @return int
     */
    protected function sortImportReports()
    {
        $iterator = $this->getImportReports()->getIterator();

        $iterator->uasort(
            function ($a, $b) {
                return ($a->getDetail('grouping').$a->getDetail('category').$a->getDetail('name') < $b->getDetail('grouping').$b->getDetail('category').$b->getDetail('name')) ? -1 : 1;
            }
        );

        $this->importReports = new ArrayCollection(iterator_to_array($iterator, false));
    }

    /**
     * @return bool
     */
    public function isDataExport(): bool
    {
        return $this->dataExport;
    }

    /**
     * DataExport.
     *
     * @param bool $dataExport
     * @return ImportManager
     */
    public function setDataExport(bool $dataExport): ImportManager
    {
        $this->dataExport = $dataExport;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDataExportAll(): bool
    {
        return $this->dataExportAll;
    }

    /**
     * DataExportAll.
     *
     * @param bool $dataExportAll
     * @return ImportManager
     */
    public function setDataExportAll(bool $dataExportAll): ImportManager
    {
        $this->dataExportAll = $dataExportAll;
        return $this;
    }

    /**
     * getImportReport
     * @param string $reportName
     * @return ImportReport|null
     */
    public function getImportReport(string $reportName): ?ImportReport
    {
        // Check custom first, this allows for local overrides
        $path = $this->getCustomImportReportDir().'/'.$reportName.'.yaml';
        if (!file_exists($path)) {
            // Next check the built-in import types folder
            $path = $this->getImportReportDir().'/'.$reportName.'.yaml';

            // Finally fail if nothing is found
            if (!file_exists($path)) {
                $path = $this->getCustomImportReportDir().'/'.$reportName.'.yml';
                if (!file_exists($path)) {
                    // Next check the built-in import types folder
                    $path = $this->getImportReportDir().'/'.$reportName.'.yml';

                    // Finally fail if nothing is found
                    if (!file_exists($path)) {
                        return null;
                    }
                }
            }
        }

        $file = new File($path, true);
        return new ImportReport($file, true);
    }

    /**
     * @return Importer
     */
    public function getImporter(): Importer
    {
        return $this->importer;
    }

    /**
     * prepareStep2
     * @param ImportReport $report
     * @param ImportControl $importControl
     * @param FormInterface $form
     */
    public function prepareStep2(ImportReport $report, ImportControl $importControl, FormInterface $form, Request $request)
    {

        $this->getImporter()->setFieldDelimiter($importControl->getFieldDelimiter());
        $this->getImporter()->setStringEnclosure($importControl->getStringEnclosure());

        if ($importControl->getCsvData() === null && $importControl->getFile() !== null) {
            $importControl->setCsvData($this->getImporter()->readFileIntoCSV($importControl->getFile()));
            unlink($importControl->getFile()->getRealPath());
            $importControl->setFile(null);
            if ('' === $importControl->getCsvData())
                throw new \Exception('No data was imported.');
            $this->getImporter()->setImportControl($importControl)->setHeaderFirstLine();
        } elseif ($importControl->getCsvData() === null) {
            $importStep2 = $request->get('import_step2');
            $importControl->setCsvData($importStep2['csvData']);
            $this->getImporter()->setImportControl($importControl)->setHeaderFirstLine();
        } elseif ($importControl->getCsvData() !== null) {
            $this->getImporter()->setImportControl($importControl)->setHeaderFirstLine();
        }

        $headings = $this->getImporter()->getHeaderRow();
        $firstLine = $this->getImporter()->getFirstRow();
        $syncKeys = [];
        foreach($report->getUniqueKeys() as $uniqueKey)
        {
            $syncKeys[$uniqueKey['label']] = $uniqueKey['name'];
        }

        // SYNC SETTINGS
        if (in_array($importControl->getMode(), ["sync", "update"])) {
            $lastFieldValue = (isset($columnOrderLast['syncField'])) ? $columnOrderLast['syncField'] : 'N';
            $lastColumnValue = (isset($columnOrderLast['syncKey'])) ? $columnOrderLast['syncKey'] : '';

            $form->add('syncField', ToggleType::class,
                [
                    'label' => 'Sync?',
                    'help' => 'Only rows with a matching database ID will be imported.',
                    'visibleByClass' => 'syncDetails',
                    'visibleWhen' => '1',
                    'wrapper_class' => 'flex-1 relative right',
                    'values' => ['1', '0'],
                ]
            )->add('syncKey', ChoiceType::class,
                [
                    'label' => 'Primary or Unique Key',
                    'data' => $lastColumnValue,
                    'help' => '{table} has these primary and unique keys "{keys}"',
                    'help_translation_parameters' => ['{table}' => $report->getDetail('table'), '{keys}' => implode('", "', array_merge([$report->getPrimaryKey()], array_keys($report->getUniqueKeys())))],
                    'choices' => $syncKeys,
                    'placeholder' => 'Please select...',
                    'row_class' => 'flex flex-col sm:flex-row justify-between content-center p-0 syncDetails',
                ]
            );
        } else {
            $form->add('syncField', HiddenType::class,
                [
                    'data' => 0,
                ]
            )->add('syncKey', HiddenType::class,
                [
                    'data' => null,
                ]
            );
        }

        $count = 0;

        $defaultColumns = function ($field) use ($report, $importControl) {
            $columns = [];

            if (!$field->isRequired() || ($importControl->getMode() === 'update' && !$report->isUniqueKey($field->getName()))) {
                $columns[Importer::COLUMN_DATA_SKIP] = 'Skip this Column';
            }
            if ($field->getArg('custom')) {
                $columns[Importer::COLUMN_DATA_CUSTOM] = 'Custom';
            }
            if ($field->getArg('function')) {
                $columns[Importer::COLUMN_DATA_FUNCTION] = 'Generate';
            }
            return $columns;
        };

        $columns = array_reduce(range(0, count($headings)-1), function ($group, $index) use (&$headings) {
            $group[strval($index)." "] = $headings[$index];
            return $group;
        }, array());

        $columnIndicators = function ($field) use ($report, $importControl) {
            $output = [];
            if ($field->isRequired() && !($importControl->getMode() === 'update' && !$report->isUniqueKey($field->getName()))) {
                $output[] = 'required';
            }
            if ($report->isUniqueKey($field->getName())) {
                $output[] = 'unique';
            }
            if ($field->isRelational()) {
                $relationalTable = $field->getRelationship()['table'] ?? '';
                $output[] = 'relational';
            }
            return $output;
        };

        foreach ($report->getFields() as $field) {
            $column = new ImportColumn();
            $column->setFlags($columnIndicators($field));
            if ($field->isHidden()) {
                $columnIndex = Importer::COLUMN_DATA_HIDDEN;
                if ($field->getArg('linked')) {
                    $columnIndex = Importer::COLUMN_DATA_LINKED;
                }
                if ($field->getArg('function') !== false) {
                    $columnIndex = Importer::COLUMN_DATA_FUNCTION;
                }
                $column->setOrder($columnIndex);
                $importControl->addColumn($column);
                continue;
            }

            $selectedColumn = '';
            foreach ($headings as $index => $columnName) {
                if (mb_strtolower($columnName) == mb_strtolower($field->getName()) || mb_strtolower($columnName) == mb_strtolower($field->getName())) {
                    $selectedColumn = $index;
                    break;
                }
            }

            $key = array_search($field->getLabel(), $headings);
            $column = new ImportColumn();
            $column->setOrder($selectedColumn)
                ->setFieldType($field->readableFieldType())
                ->setName($field->getName())
                ->setColumnChoices($defaultColumns($field), $columns)
                ->setLabel($field->getLabel())
                ->setText($firstLine[$key] ?: null)
            ;

            $importControl->addColumn($column);
        }

        $form->add('columns', CollectionType::class,
            [
                'label' => false,
                'entry_type' => ImportStepColumnType::class,
                'data' => $importControl->getColumns(),
            ]
        );

        $form->add('csvData', TextareaType::class,
            [
                'label' => 'Data',
                'help' => 'This value cannot be changed.',
                'attr' => [
                    'rows' => 4,
                    'cols' => 74,
                    'readonly' => 'readonly',
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ]
        );
    }

    /**
     * prepareStep3
     * @param ImportReport $report
     * @param ImportControl $importControl
     * @param FormInterface $form
     * @param Request $request
     * @param bool $persist
     * @return bool
     */
    public function prepareStep3(ImportReport $report, ImportControl $importControl, FormInterface $form, Request $request, bool $persist = false): bool
    {
        return $this->getImporter()->setImportControl($importControl)->setReport($report)->validateImport($persist);
    }

    /**
     * readableFileSize
     * @param $bytes
     * @return string
     */
    public function readableFileSize($bytes)
    {
        $unit=array('bytes','KB','MB','GB','TB','PB');
        return @round($bytes/pow(1024, ($i=floor(log($bytes, 1024)))), 2).' '.$unit[$i];
    }
}