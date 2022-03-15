<?php

namespace App\Domain\Center\FreePlace\Service;

use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Reference;
use App\Helper\Fields\DateTime;
use App\Helper\Fields\Text;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\File;
/**
 * Service.
 */
final class Read {

    /**
     * @var FreePlaceReadRepository
     */
    private $readRepository;

    /**
     * @var Render
     */
    private $render;

    /**
     * The constructor.
     * @param FreePlaceReadRepository $readRepository
     *
     */
    public function __construct(FreePlaceReadRepository $readRepository) {
        $this->readRepository = $readRepository;
        $this->render = new Render();
    }

    /**
     * Get applicant list
     * 
     * @param string $lang The interface language code
     * @param array<mixed> $params The get params
     *
     * @return array<mixed> $post fileds The post fields
     * 
     */
    public function list(string $lang, array $params) :array{

        $status_id = 0;
        $position_id = 0;
        if(isset($params["status_id"])) {
            $status_id = (int)$params['status_id'];
        }
        if(isset($params["position_id"])) {
            $position_id = (int)$params['position_id'];
        }
        $data = $this->readRepository->search($lang, $status_id, $position_id);

        return $this->render
                ->lang($lang)
                ->header(self::getHeader())
                ->data($this->parseData($data))
                ->build();
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
    public function getTempFile(string $lang, array $params){

        $status_id = 0;
        $position_id = 0;
        if(isset($params["status_id"])) {
            $status_id = (int)$params['status_id'];
        }
        if(isset($params["position_id"])) {
            $position_id = (int)$params['position_id'];
        }
        $data = $this->readRepository->search($lang, $status_id, $position_id);
        $data = $this->render
                ->lang($lang)
                ->header(self::getHeader())
                ->data($this->parseDataForExport($data))
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

        $tempFile = tempnam(File::sysGetTempDir(), 'phpxltmp');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        return $tempFile;
    }

    /**
     * Get header
     *
     * @param array<mixed> $header
     * 
     */
    public static function getHeader() :array{
        return array(
            "id" => Field::getInstance()->init(new Number())->execute(),
            "bin" => Field::getInstance()->init(new Number())->can_create(true)->min_length(12)->max_length(12)->execute(),
            "company_name" => Field::getInstance()->init(new Text())->execute(),
            "position_id" => Field::getInstance()->init(new Reference())->can_create(true)->can_update(true)->reference_name("position")->reference_id("id")->is_required(true)->execute(),
            "count" => Field::getInstance()->init(new Number())->can_create(true)->can_update(true)->is_required(true)->min(1)->execute(),
            //"comment" => Field::getInstance()->init(new Textarea())->can_create(true)->can_update(true)->is_visible(false)->execute(),
            "status_id" => Field::getInstance()->init(new Reference())->reference_name("place-status")->reference_id("id")->execute(),
            "created_at" => Field::getInstance()->init(new DateTime())->execute(),
        );
    }

    /**
     * Parse data from db
     *
     * @param string $lang
     * @param array<mixed> $data
     * 
     * @return array<mixed> parsed data
     */
    private function parseData(array $data):array {
        foreach($data as $i => $v) {
            foreach($v as $key => $val) {
                switch($key) {
                    case 'position_id':
                        $data[$i][$key] = array("id" => $val, "value" => $data[$i]["position_name"]);
                        unset($data[$i]["position_name"]);
                    break;
                    case 'status_id':
                        $data[$i][$key] = array("id" => $val, "value" => $data[$i]["status_name"], "color" => $data[$i]["status_color"]);
                        unset($data[$i]["status_name"]);
                        unset($data[$i]["status_color"]);
                    break;
                }
            }
        }
        return $data;
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
                        $data[$i][$key] = $data[$i]["position_name"];
                        unset($data[$i]["position_name"]);
                    break;
                    case 'status_id':
                        $data[$i][$key] = $data[$i]["status_name"];
                        unset($data[$i]["status_name"]);
                        unset($data[$i]["status_color"]);
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