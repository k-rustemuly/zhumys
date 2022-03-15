<?php
namespace App\Domain\Center\FreePlace\Service;

use App\Helper\Render;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
/**
 * Service.
 */
class Export {

    /**
     * @var Render
     */
    private $render;

    private $cells = [
        0 => 'A',
        1 => 'B',
        2 => 'C',
        3 => 'D',
        4 => 'E',
        5 => 'F',
        6 => 'G',
        7 => 'H',
        8 => 'I',
        9 => 'J',
        10 => 'K',
        11 => 'L',
        12 => 'M',
        13 => 'N',
        14 => 'O',
        15 => 'P',
        16 => 'Q',
        18 => 'R',
        18 => 'S',
        19 => 'T',
        20 => 'U',
        21 => 'V',
        22 => 'W',
        23 => 'X',
        24 => 'Y',
        25 => 'Z',
    ];

    /**
     * The constructor.
     */
    public function __construct() {
        $this->render = new Render();
    }


    /**
     * Get temporary excel file for download
     * 
     * @param string $lang The interface language code
     * @param array<mixed> $params The get params
     *
     * @return array<mixed> $post fileds The post fields
     * 
     */
    public function getSpreadsheet(string $lang, array $data){

        $data = $this->render
                ->lang($lang)
                ->header($data['header'])
                ->data($this->parseDataForExport($data['data']))
                ->build();
        $header = $this->getField($data, 'name');

        foreach($data['data'] as $k=>$v){
            $data["data"][$k] = array_merge($header['header'], $v);
        }

        $headerStyleArray = [
            'font' => [
                'bold' => true,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => [
                    'argb' => '808080',
                ],
            ]
        ];

        $dataStyleArray = [
            'font' => [
                'bold' => false,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
                'left' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                ],
            ]
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getDefaultColumnDimension()->setWidth(150, 'pt');
        $sheet->setCellValue('A1', 'No');
        $sheet->fromArray([array_values($header['header'])], Null, 'B1');
        $sheet->getStyle('A1:' . (string)( $this->cells[count($header['header'])] ) . '1')->applyFromArray($headerStyleArray);

        for ($i=1; $i <= count($data['data']); $i++) { 
            $sheet->setCellValue('A'.(string)($i+1), (string)($i));
            $sheet->fromArray([array_values($data['data'][$i-1])], Null, 'B'.(string)($i+1));
            $sheet->getStyle('A'.(string)($i+1).':' . (string)( $this->cells[count($header['header'])] ) . (string)( count($header['header']) ))->applyFromArray($dataStyleArray);
        }

        return $spreadsheet;
    }

    /**
     * Parse data from db for excel export
     *
     * @param string $lang
     * @param array<mixed> $data
     * 
     * @return array<mixed> parsed data
     */
    private function parseDataForExport(array $data):array {

        foreach($data as $i => $v) {
            foreach($v as $key => $val) {
                switch($key) {
                    case 'position_id':
                        $data[$i][$key] = $data[$i][$key]['value'];
                    break;
                    case 'status_id':
                        $data[$i][$key] = $data[$i][$key]['value'];
                    break;
                }
            }
        }
        return $data;
    }

    /**
     * Trims exact field from array
     *
     * @param string $field
     * @param array<mixed> $array
     * 
     * @return array<mixed> result
     */
    public function getField(array $array, string $field) :array{
        $result = array();
        if(isset($array["header"])) {
            $result["header"] = array_map(
                function($a) use($field) {
                    return $a[$field];
                },
                $array["header"]
            );
        }
        return $result;
    }
}