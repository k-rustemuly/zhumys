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
/**
 * Service.
 */
final class About extends Admin{

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
     * The constructor.
     * @param FreePlaceReadRepository $readRepository
     * @param LogReadRepository $logReadRepository
     *
     */
    public function __construct(FreePlaceReadRepository $readRepository, LogReadRepository $logReadRepository) {
        $this->readRepository = $readRepository;
        $this->logReadRepository = $logReadRepository;
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
        if(empty($this->freePlaceInfo)) throw new DomainException("Free place not found");
        return $this->render
        ->lang($lang)
        ->block("free_place_info", $this->getFreePlaceBlockValues())
        ->block("free_place_log_info", $this->getFreePlaceLogBlockValues($this->logReadRepository->getAllByIdAndLang($id, $lang)))
        ->build();
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
            "status_id" => Field::getInstance()->init(new Reference())->reference_name("place-status")->reference_id("id")->value(array("id" => $this->freePlaceInfo["status_id"], "value" => $this->freePlaceInfo["status_name"]))->execute(),
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
                "status_id" => Field::getInstance()->init(new Reference())->reference_name("place-status")->reference_id("id")->value(array("id" => $v["status_id"], "value" => $v["status_name"]))->execute(),
                "admin_full_name" => Field::getInstance()->init(new Text())->value($v["admin_full_name"])->execute(),
                "company_name" => Field::getInstance()->init(new Text())->value("TOO")->execute(),
                "reason" => Field::getInstance()->init(new Text())->value($v["reason"])->execute(),
                "created_at" => Field::getInstance()->init(new DateTime())->value($v["created_at"])->execute(),
            );
        }
        return $array;
    }

}