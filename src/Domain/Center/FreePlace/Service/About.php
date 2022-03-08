<?php

namespace App\Domain\Center\FreePlace\Service;

use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Textarea;
use App\Helper\Fields\Reference;
use App\Helper\Fields\DateTime;
use App\Helper\Fields\Text;
use App\Domain\FreePlace\Log\Repository\LogReadRepository;
use App\Domain\Ranging\Repository\RangingReaderRepository;

/**
 * Service.
 */
final class About {

    /**
     * @var FreePlaceReadRepository
     */
    private $readRepository;

    /**
     * @var Render
     */
    private $render;

    /**
     * @var LogReadRepository
     */
    private $logReadRepository;

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
     * Get applicant list
     * 
     * @param int $id The free place id
     * @param string $lang The interface language code
     * 
     * @return array<mixed>
     * 
     */
    public function get(int $id, string $lang) :array{
        $data = $this->readRepository->findByIdAndLang($id, $lang);

        $render = $this->render
                ->lang($lang)
                ->block("free_place_info", $this->getFreePlaceBlockValues($data))
                ->block("free_place_log_info", $this->getFreePlaceLogBlockValues($this->logReadRepository->getAllByIdAndLang($id, $lang)));
        if($data["status_id"] == 5) {
            $render = $render->block("free_place_ranging_info", $this->getFreePlaceRangingBlockValues($lang, $this->rangingReadRepository->getAllByFreePlaceIdAndLang($id, $lang)), "table");
        }
        return $render->build();
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
        return array();
    }

    /**
     * Get header
     * 
     * @param array<mixed> $header
     * 
     */
    public static function getFreePlaceBlockValues(array $data) :array{
        return array(
            "id" => Field::getInstance()->init(new Number())->value($data["id"])->execute(),
            "bin" => Field::getInstance()->init(new Number())->value($data["bin"])->execute(),
            "company_name" => Field::getInstance()->init(new Text())->value($data["company_bin"])->execute(),
            "position_id" => Field::getInstance()->init(new Reference())->value(array("id" => $data["position_id"], "value" => $data["position_name"]))->execute(),
            "count" => Field::getInstance()->init(new Number())->value($data["count"])->execute(),
            "comment" => Field::getInstance()->init(new Textarea())->value($data["comment"])->execute(),
            "status_id" => Field::getInstance()->init(new Reference())->value(array("id" => $data["status_id"], "value" => $data["status_name"], "color" => $data["status_color"]))->execute(),
            "reason" => Field::getInstance()->init(new Textarea())->value($data["reason"])->execute(),
            "created_at" => Field::getInstance()->init(new DateTime())->value($data["created_at"])->execute(),
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