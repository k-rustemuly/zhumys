<?php

namespace App\Domain\FreePlace\Service;

use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use App\Domain\FreePlace\Log\Repository\LogReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Textarea;
use App\Helper\Fields\Reference;
use App\Helper\Fields\DateTime;
use App\Domain\Company\Admin;
use App\Helper\Fields\Text;
use DomainException;
use App\Domain\Ranging\Repository\RangingReaderRepository;

/**
 * Service.
 */
final class About extends Admin {

    /**
     * @var FreePlaceReadRepository
     */
    private $readRepository;

    /**
     * @var LogReadRepository
     */
    private $logReadRepository;

    /**
     * @var Render
     */
    private $render;

    /**
     * @var array
     */
    private $freePlaceInfo;

    /**
     * @var RangingReaderRepository
     */
    private $rangingReadRepository;

    /**
     * The constructor.
     * @param FreePlaceReadRepository $readRepository
     * @param LogReadRepository $logReadRepository
     * @param RangingReaderRepository $rangingReadRepository
     *
     */
    public function __construct(FreePlaceReadRepository $readRepository,
                                LogReadRepository $logReadRepository,
                                RangingReaderRepository $rangingReadRepository) {
        $this->readRepository = $readRepository;
        $this->logReadRepository = $logReadRepository;
        $this->rangingReadRepository = $rangingReadRepository;
        $this->render = new Render();
    }

    /**
     * Get about one free place
     * 
     * @param int $id The id
     * @param string $lang The interface language code
     *
     * @return array<mixed> $post fileds The post fields
     * 
     */
    public function get(int $id, string $lang) :array{
        $this->freePlaceInfo = $this->readRepository->findByBinAndIdAndLang($this->getBin(), $id, $lang);
        if(empty($this->freePlaceInfo)) {
            throw new DomainException("Free place not found");
        } 
        $render = $this->render
                ->lang($lang)
                ->block("free_place_info", $this->getFreePlaceBlockValues())
                ->block("free_place_log_info", $this->getFreePlaceLogBlockValues($this->logReadRepository->getAllByIdAndLang($id, $lang)));
        if($this->freePlaceInfo["status_id"] >= 5) {
            $render = $render->block("free_place_ranging_info", $this->getFreePlaceRangingBlockValues($lang, $this->rangingReadRepository->getAllByFreePlaceIdAndLang($id, $lang)), "table");
        }        
        return $render->build();
    }

    /**
     * Get free place info block values
     *
     * @param array<mixed> $values
     * 
     */
    public function getFreePlaceBlockValues() :array{
        return array(
            "id" => Field::getInstance()->init(new Number())->value($this->freePlaceInfo["id"])->execute(),
            "position_id" => Field::getInstance()->init(new Reference())->reference_name("position")->reference_id("id")->value(array("id" => $this->freePlaceInfo["position_id"], "value" => $this->freePlaceInfo["position_name"]))->can_update(true)->execute(),
            "count" => Field::getInstance()->init(new Number())->is_required(true)->value($this->freePlaceInfo["count"])->can_update(true)->execute(),
            "comment" => Field::getInstance()->init(new Textarea())->value($this->freePlaceInfo["comment"])->can_update(true)->execute(),
            "status_id" => Field::getInstance()->init(new Reference())->reference_name("place-status")->reference_id("id")->value(array("id" => $this->freePlaceInfo["status_id"], "value" => $this->freePlaceInfo["status_name"], "color" => $this->freePlaceInfo["status_color"]))->execute(),
            "reason" => Field::getInstance()->init(new Text())->value($this->freePlaceInfo["reason"])->execute(),
            "created_at" => Field::getInstance()->init(new DateTime())->value($this->freePlaceInfo["created_at"])->execute(),
        );
    }

    /**
     * Get free place log info block values
     *
     * @param array<mixed> $values
     * 
     */
    public function getFreePlaceLogBlockValues(array $data) :array{
        $array = array();
        foreach ($data as $i => $v){
            $array[$i] = array(
                "admin_type_id" => Field::getInstance()->init(new Reference())->reference_name("admin")->reference_id("id")->value(array("id" => $v["admin_type_id"], "value" => $v["admin_type_name"]))->execute(),
                "status_id" => Field::getInstance()->init(new Reference())->reference_name("place-status")->reference_id("id")->value(array("id" => $v["status_id"], "value" => $v["status_name"], "color" => $v["status_color"]))->execute(),
                "admin_full_name" => Field::getInstance()->init(new Text())->value($v["admin_full_name"])->execute(),
                "company_name" => Field::getInstance()->init(new Text())->value($v["company_name"])->execute(),
                "reason" => Field::getInstance()->init(new Text())->value($v["reason"])->execute(),
                "created_at" => Field::getInstance()->init(new DateTime())->value($v["created_at"])->execute(),
            );
        }
        return $array;
    }

    /**
     * Get data for block free place ranging result
     *
     * @param string $lang
     * @param array<mixed> $data
     * 
     * @return array<mixed>
     */
    private function getFreePlaceRangingBlockValues(string $lang, array $data) :array {
        foreach ($data as $i => $v) {
            $data[$i]["status_id"] = array("id" => $v["status_id"], "value" => $v["status_name"], "color" => $v["status_color"]);
            $data[$i]["privilege_id"] = array("id" => $v["privilege_id"], "value" => $v["privilege_name"]);
            unset($data[$i]["status_name"]);
            unset($data[$i]["status_color"]);
            unset($data[$i]["privilege_name"]);
        }
        $render = new Render();
        $render->lang($lang)
                ->header(
                    array(
                        "raiting_number" => Field::getInstance()->init(new Number())->execute(),
                        "iin" => Field::getInstance()->init(new Number())->execute(),
                        "full_name" => Field::getInstance()->init(new Text())->execute(),
                        "privilege_id" => Field::getInstance()->init(new Reference())->reference_name("privilege")->reference_id("id")->execute(),
                        "status_id" => Field::getInstance()->init(new Reference())->reference_name("ranging-status")->reference_id("id")->execute(),
                    )
                )->data($data);
        return $render->build();
    }

}