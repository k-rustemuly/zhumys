<?php

namespace App\Domain\FreePlace\Service;

use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Textarea;
use App\Helper\Fields\Reference;
use App\Helper\Fields\DateTime;
use App\Domain\Company\Admin;
/**
 * Service.
 */
final class Read extends Admin{

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
        $search = $this->parseParams($params);
        $data = $this->readRepository->getAllByBinAndLang($this->getBin(), $lang);

        return $this->render
                ->lang($lang)
                ->header(self::getHeader())
                ->data($this->parseData($data))
                ->build();
    }

    /**
     * Get applicant list
     * 
     * @param array<mixed> $params The get params
     *
     * @return array<mixed> 
     * 
     */
    private function parseParams(array $params) :array{
        if(isset($params[""])){
            
        }
        return array();
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
            "position_id" => Field::getInstance()->init(new Reference())->can_create(true)->can_update(true)->reference_name("position")->reference_id("id")->is_required(true)->execute(),
            "count" => Field::getInstance()->init(new Number())->can_create(true)->can_update(true)->is_required(true)->min(1)->execute(),
            "comment" => Field::getInstance()->init(new Textarea())->can_create(true)->can_update(true)->is_visible(false)->execute(),
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
}