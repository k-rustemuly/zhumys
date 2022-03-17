<?php 
declare(strict_types=1);

namespace App\Helper;

class BaseExport{

    /**
     * @var array<mixed> Header styles for excel export
     */
    public $headerStyleArray = [
        'font' => [
            'bold' => true,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                'color' => ['argb' => '00000000'],
            ],
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'color' => [
                'argb' => '808080',
            ],
        ]
    ];

    /**
     * @var array<mixed> Data styles for excel export
     */
    public $dataStyleArray = [
        'font' => [
            'bold' => false,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                'color' => ['argb' => '00000000'],
            ],
        ]
    ];

    /**
     * Parse data from db for excel export
     *
     * @param string $lang
     * @param array<mixed> $data
     * 
     * @return array<mixed> parsed data
     */
    public function parseDataForExport(array $data):array {

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