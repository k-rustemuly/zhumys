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
     *
     */
    public function __construct(FreePlaceReadRepository $readRepository) {
        $this->readRepository = $readRepository;
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
        ->block("free_place_log_info", $this->getFreePlaceBlockValues())
        ->build();
    }

    /**
     * Get company info block values
     *
     * @param array<mixed> $values
     * 
     */
    public function getFreePlaceBlockValues() :array{
        return array(
            "id" => Field::getInstance()->init(new Number())->value($this->freePlaceInfo["id"])->execute(),
            "position_id" => Field::getInstance()->init(new Reference())->reference_name("position")->reference_id("id")->value(array("id" => $this->freePlaceInfo["position_id"], "value" => $this->freePlaceInfo["position_name"]))->execute(),
            "count" => Field::getInstance()->init(new Number())->is_required(true)->value($this->freePlaceInfo["count"])->execute(),
            "comment" => Field::getInstance()->init(new Textarea())->value($this->freePlaceInfo["comment"])->execute(),
            "status_id" => Field::getInstance()->init(new Reference())->reference_name("place-status")->reference_id("id")->value(array("id" => $this->freePlaceInfo["status_id"], "value" => $this->freePlaceInfo["status_name"]))->execute(),
            "reason" => Field::getInstance()->init(new Text())->value($this->freePlaceInfo["reason"])->execute(),
            "created_at" => Field::getInstance()->init(new DateTime())->value($this->freePlaceInfo["created_at"])->execute(),
        );
    }

}