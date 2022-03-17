<?php
namespace App\Domain\Center\FreePlace\Service;

use App\Helper\BaseExport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
/**
 * Service.
 */
class Export extends BaseExport{

    /**
     * Get Spreadsheet object for further export
     * 
     * @param array<mixed> $params The get params
     *
     * @return obj Spreadsheet
     * 
     */
    public function getSpreadsheet(array $data): Spreadsheet{

        $data['data'] = $this->parseDataForExport($data['data']);
        $header = $this->getField($data, 'name');

        $i=1;
        foreach($data['data'] as $k=>$v){
            $data["data"][$k] = array_merge($header['header'], $v);
            $data['data'][$k] = ['num'=>$i] + $data["data"][$k];
            ++$i;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getDefaultColumnDimension()->setWidth(150, 'pt');
        $sheet->setCellValue('A1', 'No');
        $sheet->fromArray([array_values($header['header'])], Null, 'B1');
        $sheet->getStyle('A1:' . (string)end($sheet->getCoordinates()))
              ->applyFromArray($this->headerStyleArray);

        $sheet->fromArray($data['data'], Null, 'A2');
        $sheet->getStyle('A2:'.(string)end($sheet->getCoordinates()))->applyFromArray($this->dataStyleArray);

        return $spreadsheet;
    }
}