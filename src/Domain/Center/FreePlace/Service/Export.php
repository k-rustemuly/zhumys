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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'No');
        $sheet->fromArray([array_values($header['header'])], Null, 'B1');

        for ($i=1; $i <= count($data['data']); $i++) { 
            $sheet->setCellValue('A'.(string)($i+1), (string)($i));
            $sheet->fromArray([array_values($data['data'][$i-1])], Null, 'B'.(string)($i+1));
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