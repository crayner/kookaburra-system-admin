<?php
/**
 * Created by PhpStorm.
 *
 * bilby
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 20/07/2019
 * Time: 16:07
 */

namespace Kookaburra\SystemAdmin\Controller;

use App\Manager\ExcelManager;
use App\Util\GlobalHelper;
use Kookaburra\UserAdmin\Util\UserHelper;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\QueryException;
use Kookaburra\SystemAdmin\Form\Entity\ImportControl;
use Kookaburra\SystemAdmin\Form\ImportStep1Type;
use Kookaburra\SystemAdmin\Form\ImportStep2Type;
use Kookaburra\SystemAdmin\Form\ImportStep3Type;
use Kookaburra\SystemAdmin\Manager\ImportManager;
use Kookaburra\SystemAdmin\Manager\ImportReport;
use Kookaburra\SystemAdmin\Manager\ImportReportField;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SystemAdminController
 * @package App\Controller

 */
class ImporterController extends AbstractController
{
    /**
     * manageImport
     * @Route("/import/manage/", name="import_manage")
     * @IsGranted("ROLE_ROUTE")
     */
    public function manageImport(ImportManager $manager)
    {
        $manager->loadImportReportList();

        return $this->render('@KookaburraSystemAdmin/Import/import_manage.html.twig',
            [
                'manager' => $manager,
            ]
        );
    }

    /**
     * exportRun
     * @param string $report
     * @param ImportManager $manager
     * @param ExcelManager $excel
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param bool $data
     * @param bool $all
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @Route("/export/{report}/{data}/run/{all}", name="export_run")
     * @IsGranted("ROLE_ROUTE")
     */
    public function exportRun(string $report, ImportManager $manager, ExcelManager $excel, Request $request, TranslatorInterface $translator, bool $data = false, bool $all = false)
    {
        $manager->setDataExport($data || true);
        $manager->setDataExportAll($all);
        $session = $request->getSession();

        $report = $manager->getImportReport($report);
        if (!$report instanceof ImportReport)
            return $this->render('components/error.html.twig',
                [
                    'error' => 'Your request failed because your inputs were invalid.',
                ]
            );

        if (!$report->isImportAccessible())
            return $this->render('components/error.html.twig',
                [
                    'error' => 'Your request failed because you do not have access to this action.',
                ]
            );


        //Create border styles
        $style_head_fill= array(
            'fill' => array('fillType' => Fill::FILL_SOLID, 'startColor' => array('rgb' => 'eeeeee'), 'endColor' => array('rgb' => 'eeeeee')),
            'borders' => array('top' => array('borderStyle' => Border::BORDER_THIN, 'color' => array('rgb' => '444444'), ), 'bottom' => array('borderStyle' => Border::BORDER_THIN, 'color' => array('rgb' => '444444'), )),
        );

        // Set document properties
        $excel->getProperties()->setCreator(UserHelper::getCurrentUser()->formatName())
            ->setLastModifiedBy(UserHelper::getCurrentUser()->formatName())
            ->setTitle($report->getDetail('name'))
        ;

        $excel->setActiveSheetIndex(0);

        $count = 0;
        $columnFields = $report->getFields();

        $columnFields = $columnFields->filter(function (ImportReportField $field) {
            return !$field->isFieldHidden();
        });

        // Create the header row
        foreach ($columnFields as $field) {
            $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($count) . '1', $translator->trans($field->getLabel()));
            $excel->getActiveSheet()->getStyle(GlobalHelper::num2alpha($count) . '1')->applyFromArray($style_head_fill);

            // Dont auto-size giant text fields
            if ($field->getArg('kind') === 'text') {
                $excel->getActiveSheet()->getColumnDimension(GlobalHelper::num2alpha($count))->setWidth(25);
            } else {
                $excel->getActiveSheet()->getColumnDimension(GlobalHelper::num2alpha($count))->setAutoSize(true);
            }

            // Add notes to column headings
            $info = ($field->isRequired()) ? "* required\n" : '';
            $info .= $this->renderView('@KookaburraSystemAdmin/field_type_component.html.twig',['type' => $field->readableFieldType()]) . "\n";
            $info .= $field->getArg('desc');
            $info = strip_tags($info);

            if (!empty($info)) {
                $excel->getActiveSheet()->getComment(GlobalHelper::num2alpha($count) . '1')->getText()->createTextRun($info);
            }

            $count++;
        }

        if ($manager->isDataExport()) {

            $data = [];
            $tableName = ucfirst($report->getDetail('table'));
            $query = $this->getDoctrine()->getManager()->createQueryBuilder();
            $query->from($report->convertTableNameToClassName($tableName), $report->getJoinAlias($tableName));

            foreach ($report->getJoin() as $fieldName => $join) {
                if (!$join->isPrimary()) {
                    $type = $join->getJoinType();
                    if ($join->getWith() === false)
                        $query->$type($report->getJoinAlias($join->getTable()) . '.' . $join->getReference(), $join->getAlias());
                    else
                        $query->$type($report->getJoinAlias($join->getTable()) . '.' . $join->getReference(), $join->getAlias(), Join::WITH, $join->getWith());
                }
            }

            foreach($report->getDetail('with') as $item)
                $query->andWhere($item);

            $select = [];
            $additional = [];
            foreach ($report->getFields() as $name=>$field) {
                if (!$field->getArg('serialise')) {
                    $w = $field->getSelect() . ' AS ' . $name;
                    $select[] = $w;
                } elseif (is_string($field->getArg('serialise')))
                    $additional[] = $field->getLabel();
            }

            $query->select($select);

            if (!$manager->isDataExportAll() && !in_array($tableName, ['SchoolYear', 'SchoolYearSpecialDay']))
            {
                // Optionally limit all exports to the current school year by default, to avoid massive files
                $schoolYear = $report->getTablesUsed();
                $field = $report->findFieldByArg('filter', 'schoolyear');
                if ($field && in_array('SchoolYear', $report->getTablesUsed()) && !$field->isFieldReadOnly()) {
                    $data['schoolYear'] = $session->get('schoolYearCurrent')->getId();
                    $query->andWhere($report->getJoinAlias('SchoolYear') . '.id = :schoolYear');
                }
            }

            $i = 0;
            foreach($report->getOrderBy() as $name=>$direction)
            {
                if ($i === 0)
                    $query->orderBy($name, $direction === 'DESC' ? 'DESC' : 'ASC');
                else
                    $query->addOrderBy($name, $direction === 'DESC' ? 'DESC' : 'ASC');
                $i = 1;
            }

            try {
                $result = $query->setParameters(array_merge(($data ?: []), $report->getFixedData()))->getQuery()->getResult();
                // dd($query,$result, $query->getQuery()->getSql());
            } catch (QueryException $e) {
                throw $e;
            }

            // Continue if there's data
            if (count($result) > 0) {

                $rowCount = 2;
                foreach ($result as $row) {
                    $row = $report->parseData($row);
                    $i = 0;
                    foreach ($row as $name=>$value) {
                        if (!$report->isHiddenField($name)) {
                            switch ($report->getFieldFilter($name)) {
                                case 'date':
                                    if (is_string($value)) {
                                        $value = \date_create_from_format($request->getSession()->get('i18n')['dateFormatPHP'] . ' H:i:s', $value . ' 00:00:00');
                                    }
                                    if ($value instanceof \DateTime)
                                        $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, $value->format('Y-m-d'));
                                    else
                                        $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, '');
                                    break;
                                case 'time':
                                    $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, null === $value ? '' : $value->format('H:i:s'));
                                    break;
                                case 'timestamp':
                                    $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, null === $value ? '' : $value->format('Y-m-d H:i:s'));
                                    break;
                                case 'yesno':
                                    $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, strtolower($value) === 'y' ? 'Yes' : 'No');
                                    break;
                                case 'array':
                                    dd($value, $row, $result);
                                    $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, strtolower($value) === 'y' ? 'Yes' : 'No');
                                    break;
                                case 'year_group_list':
                                    $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, implode(',', $report->getField($name)->transformYearGroups($value)));
                                    break;
                                case 'role_list':
                                    $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, implode(',', $report->getField($name)->transformRoles($value)));
                                    break;
                                case 'string':
                                case 'numeric':
                                case 'url':
                                case 'schoolyear':
                                case 'country':
                                case 'enum':
                                case 'html':
                                    $excel->getActiveSheet()->setCellValue(GlobalHelper::num2alpha($i++) . $rowCount, (string) $value);
                                    break;
                                default:
                                    dd($report->getFieldFilter($name));
                            }
                        }
                    }
                    $rowCount++;
                }
            }
        }

        $filename = ($manager->isDataExport()) ? 'DataExport' . '-' . $report->getDetails()->getName() : 'DataStructure' . '-' . $report->getDetails()->getName();

        $excel->setFileName($filename);

        // FINALIZE THE DOCUMENT SO IT IS READY FOR DOWNLOAD
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $excel->setActiveSheetIndex(0);

        $excel->exportWorksheet();
    }

    /**
     * importRun
     * @param string $report
     * @param ImportManager $manager
     * @param ExcelManager $excel
     * @param Request $request
     * @Route("/import/{report}/run/{step}", name="import_run")
     * @IsGranted("ROLE_ROUTE")
     */
    public function importRun(string $report, ImportManager $manager, Request $request, int $step = 1)
    {
        $memoryStart = memory_get_usage();
        $timeStart = microtime(true);
        $report = $manager->getImportReport($report);
        $importControl = new ImportControl();

        if ($step === 1) {
            $form = $this->createForm(ImportStep1Type::class, $importControl, ['action' => $this->generateUrl('system_admin__import_run', ['report' => $report->getDetails()->getName(), 'step' => $step + 1]), 'importReport' => $report]);
        } elseif ($step === 2) {
            $form = $this->createForm(ImportStep1Type::class, $importControl, ['action' => $this->generateUrl('system_admin__import_run', ['report' => $report->getDetails()->getName(), 'step' => $step]), 'importReport' => $report]);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $form = $this->createForm(ImportStep2Type::class, $importControl, [
                    'action' => $this->generateUrl('system_admin__import_run', ['report' => $report->getDetails()->getName(), 'step' => $step + 1]),
                    'importReport' => $report
                ]);
                $manager->prepareStep2($report, $importControl, $form, $request);
            } else {
                $step = 1;
            }
        } elseif ($step === 3) {
            $form = $this->createForm(ImportStep2Type::class, $importControl, [
                'action' => $this->generateUrl('system_admin__import_run', ['report' => $report->getDetails()->getName(), 'step' => $step]),
                'importReport' => $report
            ]);
            $manager->prepareStep2($report, $importControl, $form, $request);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $manager->prepareStep3($report, $importControl, $form, $request);
                $form = $this->createForm(ImportStep3Type::class, $importControl, [
                    'action' => $this->generateUrl('system_admin__import_run', ['report' => $report->getDetails()->getName(), 'step' => $step + 1]),
                    'importReport' => $report
                ]);
            } else {
                $step = 2;
            }
        } elseif ($step === 4) {
            $form = $this->createForm(ImportStep3Type::class, $importControl, [
                'action' => $this->generateUrl('system_admin__import_run', ['report' => $report->getDetails()->getName(), 'step' => 4]),
                'importReport' => $report
            ]);
            $form->handleRequest($request);
            if ($form->isValid()){
                $manager->prepareStep3($report, $importControl, $form, $request, true);
                if ($form->get('ignoreErrors') === '1')
                    $this->addFlash('warning', 'Imported with errors ignored.');
            }
        }

        return $this->render('@KookaburraSystemAdmin/Import/import_run.html.twig',
            [
                'report' => $report,
                'manager' => $manager,
                'step' => $step,
                'form' => $form->createView(),
                'executionTime' => mb_substr(microtime(true) - $timeStart, 0, 6),
                'memoryUsage' => $manager->readableFileSize(max(0, memory_get_usage() - $memoryStart)),
            ]
        );
    }

    /**
     * getProjectDir
     * @return string
     */
    private function getProjectDir(): string
    {
        return realpath(__DIR__ . '/../../../../..');
    }
    /**
     * getSettingFileName
     * @return string
     */
    private function getSettingFileName(): string
    {
        return realpath($this->getProjectDir() . '/config/packages/kookaburra.yaml');
    }
}