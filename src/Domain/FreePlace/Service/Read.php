<?php

namespace App\Domain\FreePlace\Service;

use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Text;
use App\Helper\Fields\Reference;
use App\Helper\Fields\DateTime;
use App\Helper\Fields\File;
use App\Helper\Fields\Password;
use App\Domain\Company\Admin;
use App\Domain\Position\Repository\PositionFinderRepository;
/**
 * Service.
 */
final class Read extends Admin{

    /**
     * @var FreePlaceReadRepository
     */
    private $readRepository;

    /**
     * @var PositionFinderRepository
     */
    private $positionFinderRepository;

    /**
     * @var Render
     */
    private $render;

    /**
     * The constructor.
     * @param FreePlaceReadRepository $readRepository
     *
     */
    public function __construct(FreePlaceReadRepository $readRepository, PositionFinderRepository $positionFinderRepository) {
        $this->readRepository = $readRepository;
        $this->positionFinderRepository = $positionFinderRepository;
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
        $data = $this->readRepository->getAllByBinAndLang($this->getBin(), $lang);

        return $this->render
                ->lang($lang)
                ->header(self::getHeader())
                ->data($this->parseData($data))
                ->build();
    }

    /**
     * Get header
     *
     * @param array<mixed> $header
     * 
     */
    public static function getHeader() :array{
        return array(
            "position_id" => Field::getInstance()->init(new Reference())->can_create(true)->reference_name("position")->reference_id("id")->is_required(true)->execute(),
            "count" => Field::getInstance()->init(new Number())->can_create(true)->is_required(true)->min(1)->execute(),
            "comment" => Field::getInstance()->init(new Text())->can_create(true)->is_visible(false)->execute(),
            "status_id" => Field::getInstance()->init(new Reference())->reference_name("place-status")->reference_id("id")->execute(),
            "created_at" => Field::getInstance()->init(new DateTime())->execute(),
            "sign_p12" => Field::getInstance()->init(new File())->is_visible(false)->can_create(true)->is_required(true)->execute(),
            "password" => Field::getInstance()->init(new Password())->is_visible(false)->can_create(true)->is_required(true)->execute(),
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
                        $data[$i][$key] = array("id" => $val, "value" => $data[$i]["status_name"]);
                        unset($data[$i]["status_name"]);
                    break;
                }
            }
        }
        return $data;
    }
}