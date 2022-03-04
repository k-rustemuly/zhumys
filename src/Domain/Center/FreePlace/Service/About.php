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
     * @param int $id The free place id
     * @param string $lang The interface language code
     * 
     * @return array<mixed>
     * 
     */
    public function get(int $id, string $lang) :array{
        $data = $this->readRepository->findByIdAndLang($id, $lang);

        return $this->render
                ->lang($lang)
                ->block("free_place_info", $this->getFreePlaceBlockValues($data))
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
            "status_id" => Field::getInstance()->init(new Reference())->value(array("id" => $data["status_id"], "value" => $data["status_name"]))->execute(),
            "created_at" => Field::getInstance()->init(new DateTime())->value($data["created_at"])->execute(),
        );
    }

}