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
 * Date: 20/09/2019
 * Time: 13:48
 */

namespace Kookaburra\SystemAdmin\Manager;

use App\Provider\ProviderFactory;
use App\Util\TranslationsHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Driver\PDOException;
use Kookaburra\SystemAdmin\Form\Entity\ImportColumn;
use Kookaburra\SystemAdmin\Form\Entity\ImportControl;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class Importer
 * @package App\Manager\SystemAdmin
 */
class Importer
{
    const COLUMN_DATA_SKIP = -1;
    const COLUMN_DATA_CUSTOM = -2;
    const COLUMN_DATA_FUNCTION = -3;
    const COLUMN_DATA_LINKED = -4;
    const COLUMN_DATA_HIDDEN = -5;

    const ERROR_IMPORT_FILE = 'There was an error reading the file {value}.';

    const WARNING_DUPLICATE = 'A duplicate entry already exists for this record. Record skipped.';

    /**
     * @var int
     */
    private $errorID = 0;

    /**
     * @var string
     */
    private $fieldDelimiter = ',';

    /**
     * @var string
     */
    private $stringEnclosure = '"';

    /**
     * @var array
     */
    private $headerRow = [];

    /**
     * @var array
     */
    private $firstRow = [];

    /**
     * @var ImportReport
     */
    private $report;

    /**
     * @var ImportControl
     */
    private $importControl;

    /**
     * @var array|null
     */
    private $importData;

    /**
     * @var ConstraintViolationList
     */
    private $violations;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $importSuccess = false;

    /**
     * @var bool
     */
    private $buildSuccess = true;

    /**
     * @var bool
     */
    private $databaseSuccess = true;

    /**
     * @var int
     */
    private $processedRows = 0;

    /**
     * @var int
     */
    private $processedErrorRows = 0;

    /**
     * @var int
     */
    private $processedErrors = 0;

    /**
     * @var int
     */
    private $processedWarnings = 0;

    /**
     * @var int
     */
    private $inserts = 0;

    /**
     * @var int
     */
    private $inserts_skipped = 0;

    /**
     * @var int
     */
    private $updates = 0;

    /**
     * @var int
     */
    private $updates_skipped = 0;

    /**
     * @var array
     */
    private $trueValues = [];

    /**
     * @var bool
     */
    private $emptyData = false;

    /**
     * Importer constructor.
     * @param Validation $validation
     */
    public function __construct(ValidatorInterface $validator, LoggerInterface $logger, string $timeZone)
    {
        $this->validator = $validator;
        $this->logger = $logger;
        $this->logger->setTimeZone(new \DateTimeZone($timeZone));
    }

    /**
     * @return string
     */
    public function getFieldDelimiter(): string
    {
        return $this->fieldDelimiter;
    }

    /**
     * FieldDelimiter.
     *
     * @param string $fieldDelimiter
     * @return Importer
     */
    public function setFieldDelimiter(string $fieldDelimiter): Importer
    {
        $this->fieldDelimiter = $fieldDelimiter;
        return $this;
    }

    /**
     * @return string
     */
    public function getStringEnclosure(): string
    {
        return $this->stringEnclosure;
    }

    /**
     * StringEnclosure.
     *
     * @param string $stringEnclosure
     * @return Importer
     */
    public function setStringEnclosure(string $stringEnclosure): Importer
    {
        $this->stringEnclosure = $stringEnclosure;
        return $this;
    }

    /**
     * @return int
     */
    public function getErrorID(): int
    {
        return $this->errorID;
    }

    /**
     * ErrorID.
     *
     * @param int $errorID
     * @return Importer
     */
    public function setErrorID(int $errorID): bool
    {
        $this->errorID = $errorID;
        return false;
    }

    /**
     * @return array
     */
    public function getHeaderRow(): array
    {
        return $this->headerRow;
    }

    /**
     * HeaderRow.
     *
     * @param array $headerRow
     * @return Importer
     */
    public function setHeaderRow(array $headerRow): Importer
    {
        $this->headerRow = $headerRow;
        return $this;
    }

    /**
     * @return array
     */
    public function getFirstRow(): array
    {
        return $this->firstRow;
    }

    /**
     * FirstRow.
     *
     * @param array $firstRow
     * @return Importer
     */
    public function setFirstRow(array $firstRow): Importer
    {
        $this->firstRow = $firstRow;
        return $this;
    }

    /**
     * readFileIntoCSV
     * @return bool|string|string[]|null
     */
    public function readFileIntoCSV(File $file)
    {
        $data = '';
        $extension = $file->guessExtension();
        if ($extension === null) {
            $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        }
        if ($extension === 'csv') {
            $opts = array('http' => array('header' => "Accept-Charset: utf-8;q=0.7,*;q=0.7\r\n"."Content-Type: text/html; charset =utf-8\r\n"));
            $context = stream_context_create($opts);

            $data = file_get_contents($file->getRealPath(), false, $context);
            if (mb_check_encoding($data, 'UTF-8') == false) {
                $data = mb_convert_encoding($data, 'UTF-8');
            }

            // Grab the header & first row for Step 1
            if ($csvData = $this->readCSVFile($file->getRealPath())) {
                $this->setHeaderFirstLine($csvData);
            } else {
                $this->errorID = Importer::ERROR_IMPORT_FILE;
                return false;
            }
        } elseif (in_array($extension, ['xlsx','xls','xml','ods'])) {
            // Try to use the best reader if available, otherwise catch any read errors
            try {
                if ($file->guessExtension() === 'xml') {
                    $objReader = IOFactory::load($file->getRealPath());
                    $objPHPExcel = $objReader->load($file->getRealPath());
                } else {
                    $objPHPExcel = IOFactory::load($file->getRealPath());
                }
            } catch (Exception $e) {
                return $this->setErrorID(Importer::ERROR_IMPORT_FILE);
            }

            $objWorksheet = $objPHPExcel->getActiveSheet();
            $lastColumn = $objWorksheet->getHighestColumn();

            // Grab the header & first row for Step 1
            foreach ($objWorksheet->getRowIterator(0, 2) as $rowIndex => $row) {
                $array = $objWorksheet->rangeToArray('A'.$rowIndex.':'.$lastColumn.$rowIndex, null, true, true, false);

                if ($rowIndex == 1) {
                    $this->setHeaderRow($array[0]);
                } elseif ($rowIndex == 2) {
                    $this->setFirstRow($array[0]);
                }
            }

            $objWriter = IOFactory::createWriter($objPHPExcel, 'Csv');

            // Export back to CSV
            ob_start();
            $objWriter->save('php://output');
            $data = ob_get_clean();
        }

        return $data;
    }

    /**
     * Read CSV File
     *
     * @param  string  Full File Path
     * @return  bool  true on success
     */
    public function readCSVFile($csvFile)
    {
        return file_get_contents($csvFile);
    }

    /**
     * setHeaderFirstLine
     * @param string $data
     * @return $this
     */
    public function setHeaderFirstLine(?string $data = null): Importer
    {
        $data = $this->readCSVString($data);
        if ([] === $data)
            return $this->setEmptyData(true);
        if (!isset($data[0]))
            $data[0] = $data;

        $this->setHeaderRow(array_keys($data[0]));
        $this->setFirstRow(array_values($data[0]));
        return $this;
    }

    /**
     * readCSVString
     * @param string|null $data
     * @return array
     */
    public function readCSVString(?string $data = null): array
    {
        if ($data === null)
            $data = $this->getImportControl()->getCsvData();

        $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
        return $this->importData = $serializer->decode($data, 'csv');

    }

    /**
     * @return ImportReport
     */
    public function getReport(): ImportReport
    {
        return $this->report;
    }

    /**
     * Report.
     *
     * @param ImportReport $report
     * @return Importer
     */
    public function setReport(ImportReport $report): Importer
    {
        $this->report = $report;
        return $this;
    }

    /**
     * @return ImportControl
     */
    public function getImportControl(): ImportControl
    {
        return $this->importControl;
    }

    /**
     * ImportControl.
     *
     * @param ImportControl $importControl
     * @return Importer
     */
    public function setImportControl(ImportControl $importControl): Importer
    {
        $this->importControl = $importControl;
        return $this;
    }

    /**
     * isIgnoreError
     * @return bool
     */
    public function isIgnoreError(): bool
    {
        return $this->getImportControl()->isIgnoreErrors();
    }

    /**
     * validateImport
     * @param bool $persist
     * @return bool
     */
    public function validateImport(bool $persist = false): bool
    {
        $table = $this->getReport()->convertTableNameToClassName($this->getReport()->getDetail('table'));

        $line = 2;
        if ($persist) {
            $em = ProviderFactory::getEntityManager();
            $em->beginTransaction();
            $this->getLogger()->notice(TranslationsHelper::translate('The import is attempting to write to the database for table "{table}"', ['{table}' => $table]));
        }
        foreach($this->readCSVString() as $data)
        {
            $this->convertData($data);
            $entity = new $table();
            $rowError = false;
            if (in_array($this->getImportControl()->getMode(), ['sync','update'])) {
                $syncKey = 'id';
                if ($this->getImportControl()->isSyncField())
                    $syncKey = $this->getImportControl()->getSyncKey();
                $uniqueKey = $this->getReport()->getUniqueKey($syncKey);

                if (null === $uniqueKey && $this->getImportControl()->getMode() === 'update') {
                    $this->incrementUpdatesSkipped()
                        ->incrementProcessedRows();
                    $this->getLogger()->warning(TranslationsHelper::translate('Missing value for a required field.'), ['line' => $line, 'cause' => $table, 'propertyPath' => $uniqueKey]);
                    $line++;
                    continue;
                } elseif (null !== $uniqueKey) {
                    $search = [];
                    foreach($uniqueKey['fields'] as $fieldName) {
                        $field = $this->getReport()->getField($fieldName);
                        $search[$fieldName] = $this->getValue($field, $data);
                    }
                    $entity = ProviderFactory::getRepository($table)->findOneBy($search);
                }

                if (null === $entity && $this->getImportControl()->getMode() === 'update') {
                    $this->incrementUpdatesSkipped()
                        ->incrementProcessedRows();
                    $this->getLogger()->warning(TranslationsHelper::translate('A database entry for this record could not be found. Record skipped.'), ['line' => $line, 'cause' => $table, 'propertyPath' => $uniqueKey, 'value' => $field->getValue($data[$field->getLabel()], $this->getTrueValues())]);
                    $line++;
                    continue;
                }
            }

            if (null === $entity && in_array($this->getImportControl()->getMode(), ['sync','insert']))
                $entity = new $table();

            $columnID = 0;
            if (!$entity) {
                $this->incrementProcessedRows();
                continue;
            }

            $validationList = new ConstraintViolationList();
            foreach($this->getTrueValues() as $label=>$value)
            {
                $importColumn = $this->getImportControl()->getColumns()->get($columnID);
                $value = $this->getTrueValues()->slice($importColumn->getOrder(), 1);
                $value = reset($value);

                if (!$value->field->getArg('readonly') && Importer::COLUMN_DATA_SKIP !== $importColumn->getOrder()) {
                    $setName = 'set' . ucfirst($importColumn->getName());
                    if (!method_exists($entity, $setName))
                        dd($entity, $setName, $this, 'The entity should have a method '.$setName.'!');
                    $entity->$setName($value->value);
                }
                $columnID++;
                if (isset($value->violations) && $value->violations->count() > 0)
                    $validationList->addAll($value->violations);
            }

            $validationList->addAll($this->getValidator()->validate($entity));
            foreach($validationList as $violation)
            {
                $message = $violation->getMessage();
                $invalidValue = $violation->getInvalidValue();
                $propertyPath = $violation->getPropertyPath();
                $level = 'error';
                if ($violation->getConstraint() instanceof UniqueEntity)
                {
                    $message = Importer::WARNING_DUPLICATE;
                    $level = 'warning';
                    $invalidValue = $this->correctUniqueInvalidValue($violation);
                    $propertyPath = $this->correctUniquePropertyPath($violation);
                    $rowError = true;
                }

                $this->getViolations()->add($withLine = new ConstraintViolation(
                    $message,
                    $violation->getMessageTemplate(),
                    array_merge($violation->getParameters(), ['line' => $line, 'level' => $level]),
                    $violation->getRoot(),
                    $propertyPath,
                    $invalidValue,
                    $violation->getPlural(),
                    $violation->getCode(),
                    $violation->getConstraint(),
                    $violation->getCause()
                ));
                $this->addLogMessage($withLine);
                if ($level === 'error') {
                    $this->incrementProcessedErrors();
                    $rowError = true;
                } else
                    $this->incrementProcessedWarnings();
            }

            if ($rowError && $level === 'error') {
                $this->incrementProcessedErrorRows();
            }

            $line++;
            $this->incrementProcessedRows();
            if ($rowError && $entity->getId() > 0 && $persist)
                $em->refresh($entity);

            if ($entity->getId() > 0) {
                if ($rowError)
                    $this->incrementUpdatesSkipped();
                else {
                    $this->incrementUpdates();
                    if ($persist) {
                        $em->persist($entity);
                        $this->getLogger()->notice(TranslationsHelper::translate('The importer updated a record "{id}" into the table "{table}"', ['{id}' => $entity->__toString(), '{table}' => get_class($entity)]), ['target' => $table, 'id' => $entity->__toString()]);
                    }
                }
            } else {
                if ($rowError)
                    $this->incrementInsertsSkipped();
                else {
                    $this->incrementInserts();
                    if ($persist) {
                        $em->persist($entity);
                        $this->getLogger()->notice(TranslationsHelper::translate('The importer inserted a record "{id}" into the table "{table}"', ['{id}' => $entity->__toString(), '{table}' => get_class($entity)]), ['target' => $table, 'id' => $entity->__toString()]);
                    }
                }
            }
        }

        $this->setImportSuccess(true);

        if ($this->getInserts() + $this->getUpdates() > 0 && $persist)
        {
            try {
                $em->flush();
                $em->commit();
                $this->getLogger()->notice(TranslationsHelper::translate('importer_database_commit', ['count' => $this->getInserts() + $this->getUpdates()]), ['target' => $table]);
            } catch (PDOException $e) {
                $em->rollback();
                $this->setImportSuccess(false);
                $this->getLogger()->error(TranslationsHelper::translate('The database failed to import, and was rolled back.'), ['target' => $table]);
            }
        } elseif ($persist)
            $this->getLogger()->notice(TranslationsHelper::translate('importer_database_commit', ['count' => 0]), ['target' => $table]);


        return $this->isImportSuccess();
    }

    /**
     * getValidator
     * @return ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
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
     * setViolations
     * @param ConstraintViolationList $violations
     * @return Importer
     */
    public function setViolations(ConstraintViolationList $violations): Importer
    {
        $this->violations = $violations;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Logger.
     *
     * @param LoggerInterface $logger
     * @return Importer
     */
    public function setLogger(LoggerInterface $logger): Importer
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * addLogMessage
     * @param ConstraintViolation $violation
     * @return Importer
     */
    private function addLogMessage(ConstraintViolation $violation): Importer
    {
        $level = $violation->getParameters()['level'] ?: 'error';
        if ($level === 'error')
            $this->setBuildSuccess(false);
        $this->getLogger()->$level(TranslationsHelper::translate($violation->getMessage(), $violation->getParameters()), [
            'line' => $violation->getParameters()['line'],
            'propertyPath' => $violation->getPropertyPath(),
            'invalidValue' => $violation->getInvalidValue(),
            'cause' => $violation->getCause(),
            'constraint' => get_class($violation->getConstraint()),
        ]);
        return $this;
    }

    /**
     * @return bool
     */
    public function isImportSuccess(): bool
    {
        return $this->importSuccess;
    }

    /**
     * ImportSuccess.
     *
     * @param bool $importSuccess
     * @return Importer
     */
    public function setImportSuccess(bool $importSuccess): Importer
    {
        $this->importSuccess = $importSuccess;
        return $this;
    }

    /**
     * @return bool
     */
    public function isBuildSuccess(): bool
    {
        return $this->buildSuccess;
    }

    /**
     * BuildSuccess.
     *
     * @param bool $buildSuccess
     * @return Importer
     */
    public function setBuildSuccess(bool $buildSuccess): Importer
    {
        $this->buildSuccess = $buildSuccess;
        return $this;
    }

    /**
     * @return int
     */
    public function getProcessedRows(): int
    {
        return $this->processedRows = $this->processedRows ?: 0;
    }

    /**
     * incrementProcessedRows
     * @return Importer
     */
    public function incrementProcessedRows(): Importer
    {
        return $this->setProcessedRows($this->getProcessedRows() + 1);
    }

    /**
     * ProcessedRows.
     *
     * @param int $processedRows
     * @return Importer
     */
    public function setProcessedRows(int $processedRows): Importer
    {
        $this->processedRows = $processedRows;
        return $this;
    }

    /**
     * getProcessedErrorRows
     * @return int
     */
    public function getProcessedErrorRows(): int
    {
        return $this->processedErrorRows = $this->processedErrorRows ?: 0;
    }

    /**
     * incrementProcessedErrorRows
     * @return Importer
     */
    public function incrementProcessedErrorRows(): Importer
    {
        return $this->setProcessedErrorRows($this->getProcessedErrorRows() + 1);
    }

    /**
     * ProcessedErrorRows.
     *
     * @param int $processedErrorRows
     * @return Importer
     */
    public function setProcessedErrorRows(int $processedErrorRows): Importer
    {
        $this->processedErrorRows = $processedErrorRows;
        return $this;
    }

    /**
     * @return int
     */
    public function getProcessedErrors(): int
    {
        return $this->processedErrors = $this->processedErrors ?: 0;
    }

    /**
     * ProcessedErrors.
     *
     * @param int $processedErrors
     * @return Importer
     */
    public function setProcessedErrors(int $processedErrors): Importer
    {
        $this->processedErrors = $processedErrors;
        return $this;
    }

    /**
     * incrementProcessedErrors
     * @return Importer
     */
    private function incrementProcessedErrors(): Importer
    {
        return $this->setProcessedErrors($this->getProcessedErrors() + 1);
    }

    /**
     * @return int
     */
    public function getProcessedWarnings(): int
    {
        return $this->processedWarnings = $this->processedWarnings ?: 0;
    }

    /**
     * ProcessedWarnings.
     *
     * @param int $processedWarnings
     * @return Importer
     */
    public function setProcessedWarnings(int $processedWarnings): Importer
    {
        $this->processedWarnings = $processedWarnings;
        return $this;
    }

    /**
     * incrementProcessedErrors
     * @return Importer
     */
    private function incrementProcessedWarnings(): Importer
    {
        return $this->setProcessedWarnings($this->getProcessedWarnings() + 1);
    }

    /**
     * @return bool
     */
    public function isDatabaseSuccess(): bool
    {
        return $this->databaseSuccess;
    }

    /**
     * DatabaseSuccess.
     *
     * @param bool $databaseSuccess
     * @return Importer
     */
    public function setDatabaseSuccess(bool $databaseSuccess): Importer
    {
        $this->databaseSuccess = $databaseSuccess;
        return $this;
    }

    /**
     * getInserts
     * @return int
     */
    public function getInserts(): int
    {
        return $this->inserts =  $this->inserts ?: 0;
    }

    /**
     * incrementInserts
     * @return Importer
     */
    public function incrementInserts(): Importer
    {
        return $this->setInserts($this->getInserts() + 1);
    }

    /**
     * setInserts
     * @param int $inserts
     * @return Importer
     */
    public function setInserts(int $inserts): Importer
    {
        $this->inserts = $inserts;
        return $this;
    }

    /**
     * @return int
     */
    public function getInsertsSkipped(): int
    {
        return $this->inserts_skipped =  $this->inserts_skipped ?: 0;
    }

    /**
     * InsertsSkipped.
     *
     * @param int $inserts_skipped
     * @return Importer
     */
    public function incrementInsertsSkipped(): Importer
    {
        return $this->setInsertsSkipped($this->getInsertsSkipped() + 1);
;    }

    /**
     * InsertsSkipped.
     *
     * @param int $inserts_skipped
     * @return Importer
     */
    public function setInsertsSkipped(int $inserts_skipped): Importer
    {
        $this->inserts_skipped = $inserts_skipped;
        return $this;
    }

    /**
     * @return int
     */
    public function getUpdates(): int
    {
        return $this->updates = $this->updates ?: 0;
    }

    /**
     * incrementUpdates
     * @return Importer
     */
    public function incrementUpdates(): Importer
    {
        return $this->setUpdates($this->getUpdates() + 1);
    }

    /**
     * Updates.
     *
     * @param int $updates
     * @return Importer
     */
    public function setUpdates(int $updates): Importer
    {
        $this->updates = $updates;
        return $this;
    }

    /**
     * @return int
     */
    public function getUpdatesSkipped(): int
    {
        return $this->updates_skipped = $this->updates_skipped ?: 0;
    }

    /**
     * incrementUpdatesSkipped
     * @return Importer
     */
    public function incrementUpdatesSkipped(): Importer
    {
        return $this->setUpdatesSkipped($this->getUpdatesSkipped() + 1);
    }

    /**
     * UpdatesSkipped.
     *
     * @param int $updates_skipped
     * @return Importer
     */
    public function setUpdatesSkipped(int $updates_skipped): Importer
    {
        $this->updates_skipped = $updates_skipped;
        return $this;
    }

    /**
     * getValue
     * @param $field
     * @param $data
     */
    public function getValue($field, $data)
    {
        if ($field instanceof ImportReportField)
            $field = $field->getName();

        $control = $this->getImportControl()->getColumns()->filter(function($column) use ($field) {
            return $column->getName() === $field;
        })->first();

        $control = $this->getImportControl()->getColumns()->get($control->getOrder());
        $field = $this->getReport()->getField($control->getName());

        return $field->getValue($data[$field->getLabel()], $this->getTrueValues());
    }

    /**
     * correctUniqueInvalidValue
     * @param ConstraintViolation $violation
     * @return string
     */
    private function correctUniqueInvalidValue(ConstraintViolation $violation): string
    {
        $result = $violation->getInvalidValue();
        if (count($violation->getConstraint()->fields) > 1){
            $result = '';
            $root = $violation->getRoot();
            foreach($violation->getConstraint()->fields as $fieldName) {
                $get = 'get'.ucfirst($fieldName);
                if (method_exists($root, $get)){
                    $value = $root->$get();
                    if (is_string($value)) {
                        $result .= $value . ', ';
                    } else {
                        if (method_exists($value, '__toString'))
                            $value = $value->__toString();
                        elseif (method_exists($value, 'getName'))
                            $value = $value->getName();
                        elseif (method_exists($value, 'getId'))
                            $value = $value->getId();
                        else
                            $value = '';
                        $result .= $value . ', ';
                    }
                }
            }
        }

        return trim($result, ', ');
    }

    /**
     * correctUniqueInvalidValue
     * @param ConstraintViolation $violation
     * @return string
     */
    private function correctUniquePropertyPath(ConstraintViolation $violation): string
    {
        $result = $violation->getPropertyPath();
        if (count($violation->getConstraint()->fields) > 1){
            $result = '';
            foreach($violation->getConstraint()->fields as $fieldName) {
                $result .= $fieldName . ', ';
            }
        }

        return trim($result, ', ');
    }

    /**
     * convertData
     * @param array $data
     */
    private function convertData(array $data)
    {
        $this->setTrueValues(new ArrayCollection());
        $count = 0;
        foreach ($data as $label=>$value)
        {
            if ($label === '')
                continue;

            if ($this->getReport()->findFieldByLabel($label)->getArg('serialise') === false) {
                $this->trueValues[$label] = new \stdClass();
                $this->trueValues[$label]->count = $count++;
                $this->trueValues[$label]->found = false;
                $this->trueValues[$label]->field = $this->getReport()->findFieldByLabel($label);
                $this->trueValues[$label]->value = null;
                $this->trueValues[$label]->was = $value;
            }
            else
            {
                $serialise = $this->getReport()->findFieldByLabel($label)->getArg('serialise');
                if (!isset($this->trueValues[$serialise]))
                {
                    $this->trueValues[$serialise] = new \stdClass();
                    $this->trueValues[$serialise]->count = $count++;
                    $this->trueValues[$serialise]->found = false;
                    $this->trueValues[$serialise]->field = new ImportReportField($serialise, ['label' => $serialise, 'select' => $this->getReport()->findFieldByLabel($label)->getSelect(), 'args' => ['filter' => 'array', 'hidden' => true]]);
                    $this->trueValues[$serialise]->value = null;
                    $this->trueValues[$serialise]->was = [];
                }
                $this->trueValues[$serialise]->was = array_merge($this->trueValues[$serialise]->was, [$label => $value]);
            }
        }

        do {
            $again = false;
            $loop = 0;
            foreach($this->getTrueValues() as $label=>$w)
            {
                if (!$w->found) {
                    if ($w->field instanceof ImportReportField) {
                        $value = $w->field->getValue($this->getTrueValues()[$label]->was, $this->getTrueValues());
                    } else {

                        dd($w,$label);
                    }
                    if (null !== $value)
                    {
                        $w->found = true;
                        $w->value = $value;
                    } else {
                        if (in_array($data[$label], ['',null, 0]))
                            $w->found = true;
                    }
                }

                if (!$w->found)
                    $again = true;
                if (++$loop > 3)
                    $again = false;
            }
        } while ($again);
        $columns = new ArrayCollection();
        $count = 0;
        foreach($this->getTrueValues() as $label=>$W) {
            $wasColumn = $this->getImportControl()->getColumns()->filter(function(ImportColumn $column) use ($label) {
                return $column->getLabel() === $label;
            });
            $column = new ImportColumn();
            if ($wasColumn->count() === 1) {
                $wasColumn = $wasColumn->first();
                $column->setName($wasColumn->getName());
                $column->setOrder($wasColumn->getOrder());
                $column->setText($w->value);
                $column->setColumnChoices($wasColumn->getColumnChoices(), []);
                $column->setFlags($wasColumn->getFlags());
                $column->setFieldType($wasColumn->getFieldType());
                $column->setLabel($label);
            } else {
                $field = $this->getReport()->getFieldByLabel($label);
                $column->setName($field->getName());
                $column->setOrder($count);
                $column->setText($w->value);
                $column->setColumnChoices([], []);
                $column->setFlags([]);
                $column->setFieldType([]);
                $column->setLabel($label);
            }
            $columns->add($column);
            $count++;
        }
        $this->getImportControl()->setColumns($columns);
    }

    /**
     * getTrueValues
     * @return ArrayCollection
     */
    public function getTrueValues(): ArrayCollection
    {
        return $this->trueValues = $this->trueValues ?: new ArrayCollection();
    }

    /**
     * setTrueValues
     * @param ArrayCollection $trueValues
     * @return Importer
     */
    public function setTrueValues(ArrayCollection $trueValues): Importer
    {
        $this->trueValues = $trueValues;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEmptyData(): bool
    {
        return $this->emptyData;
    }

    /**
     * EmptyData.
     *
     * @param bool $emptyData
     * @return Importer
     */
    public function setEmptyData(bool $emptyData): Importer
    {
        $this->emptyData = $emptyData;
        return $this;
    }

}