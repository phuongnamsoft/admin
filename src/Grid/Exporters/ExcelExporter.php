<?php

namespace PNS\Admin\Grid\Exporters;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * XLSX export using PhpSpreadsheet only (no maatwebsite/excel).
 * Upstream depends on Maatwebsite\Excel; this project keeps phpoffice/phpspreadsheet ^2.0.
 */
abstract class ExcelExporter extends AbstractExporter
{
    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var array
     */
    protected $headings = [];

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @return array
     */
    public function headings(): array
    {
        if (!empty($this->columns)) {
            return array_values($this->columns);
        }

        return $this->headings;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function query()
    {
        if (!empty($this->columns)) {
            $columns = array_keys($this->columns);

            $eagerLoads = array_keys($this->getQuery()->getEagerLoads());

            $columns = collect($columns)->reject(function ($column) use ($eagerLoads) {
                return Str::contains($column, '.') || in_array($column, $eagerLoads);
            });

            return $this->getQuery()->select($columns->toArray());
        }

        return $this->getQuery();
    }

    /**
     * Column keys aligned with headings (same strategy as Laravel Excel FromQuery + WithHeadings).
     *
     * @return array<int, string>
     */
    protected function exportColumnKeys(): array
    {
        if ($this->columns !== []) {
            return array_keys($this->columns);
        }

        $h = $this->headings;
        if ($h === []) {
            return [];
        }

        $keys = array_keys($h);

        return $keys === range(0, count($h) - 1) ? [] : $keys;
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headings = $this->headings();
        $col = 1;
        foreach ($headings as $heading) {
            $sheet->setCellValue([$col++, 1], $heading);
        }

        $keys = $this->exportColumnKeys();
        $eloquentModel = $this->grid->getFilter()->getModel()->eloquent();
        $pk = $eloquentModel->getQualifiedKeyName();

        $exportQuery = clone $this->query();
        if (empty($exportQuery->getQuery()->orders)) {
            $exportQuery->orderBy($pk);
        }

        $rowIndex = 2;
        foreach ($exportQuery->lazy(1000) as $model) {
            if ($keys === []) {
                $keys = array_keys($model->toArray());
                $headingCount = count($headings);
                if ($headingCount > 0 && count($keys) > $headingCount) {
                    $keys = array_slice($keys, 0, $headingCount);
                }
            }

            $array = $model->toArray();
            $colIndex = 1;
            foreach ($keys as $key) {
                $value = Arr::get($array, $key, $model->getAttribute($key));
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }
                $sheet->setCellValue([$colIndex++, $rowIndex], $value);
            }
            $rowIndex++;
        }

        $this->sendSpreadsheet($spreadsheet);
    }

    private function sendSpreadsheet(Spreadsheet $spreadsheet): void
    {
        $filename = $this->fileName ?? 'export.xlsx';
        $filename = basename(str_replace(["\0", '/', '\\'], '', $filename));
        if ($filename === '') {
            $filename = 'export.xlsx';
        }

        $writer = new Xlsx($spreadsheet);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        $spreadsheet->disconnectWorksheets();

        exit;
    }
}
