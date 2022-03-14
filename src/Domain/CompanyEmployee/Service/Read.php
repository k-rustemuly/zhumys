<?php

namespace App\Domain\CompanyEmployee\Service;

use App\Domain\CompanyEmployee\Repository\CompanyEmployeeReadRepository as ReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Text;
use App\Helper\Fields\Date;
use App\Helper\Fields\PhoneNumber;
use App\Helper\Fields\Reference;
use App\Helper\Fields\Textarea;
use App\Helper\Fields\Email;
use App\Helper\Fields\Tag;
use App\Helper\Fields\DateTime;
use App\Domain\Position\Repository\PositionFinderRepository;

/**
 * Service.
 */
final class Read {

    /**
     * @var ReadRepository
     */
    private $readRepository;

    /**
     * @var PositionFinderRepository
     */
    private $positionReadRepository;

    /**
     * @var Render
     */
    private $render;

    /**
     * The constructor.
     * @param ReadRepository $readRepository
     * @param PositionFinderRepository $positionReadRepository
     *
     */
    public function __construct(ReadRepository $readRepository, PositionFinderRepository $positionReadRepository) {
        $this->readRepository = $readRepository;
        $this->positionReadRepository = $positionReadRepository;
        $this->render = new Render();
    }

    /**
     * Get applicant list
     * 
     * @param string $lang The interface language code
     *
     * @return array<mixed> $post fileds The post fields
     * 
     */
    public function list(string $lang, array $params = array()) :array{
        $list = $this->readRepository->getAllByLang($lang);

        return $this->render
                ->lang($lang)
                ->header(self::getHeader())
                ->data($this->parseData($list, $lang))
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
            //"raiting_number" => Field::getInstance()->init(new Number())->execute(),
            //"iin" => Field::getInstance()->init(new Number())->is_visible(false)->execute(),
            "full_name" => Field::getInstance()->init(new Text())->execute(),
            "birthdate" => Field::getInstance()->init(new Date())->execute(),
            //"email" => Field::getInstance()->init(new Email())->is_visible(false)->execute(),
            "phone_number" => Field::getInstance()->init(new PhoneNumber())->execute(),
            //"address" => Field::getInstance()->init(new Textarea())->is_visible(false)->execute(),
            //"second_phone_number" => Field::getInstance()->init(new Textarea())->is_visible(false)->execute(),
            "privilege_id" => Field::getInstance()->init(new Reference())->reference_name("privilege")->reference_id("id")->execute(),
            //"status_id" => Field::getInstance()->init(new Reference())->reference_name("applicant-status")->reference_id("id")->execute(),
            "positions" => Field::getInstance()->init(new Tag())->tag_name("position")->tag_id("id")->tag_show("name")->is_visible(true)->can_create(true)->can_update(true)->is_required(true)->execute(),
            // "is_have_whatsapp" => Field::getInstance()->init(new Boolean())->execute(),
            // "is_have_telegram" => Field::getInstance()->init(new Boolean())->execute(),
            //"comment" => Field::getInstance()->init(new Textarea())->is_visible(false)->can_create(true)->can_update(true)->execute(),
            "created_at" => Field::getInstance()->init(new DateTime())->execute(),
        );
    }

    /**
     * Parse data
     * 
     * @param array $data
     * @param string $lang
     * 
     * @return array<mixed>
     */
    private function parseData(array $data, string $lang) :array{
        foreach ($data as $i => $v) {
            $data[$i]["privilege_id"] = array("id" => $v["privilege_id"], "value" => $v["privilege_name"]);
            unset($data[$i]["privilege_name"]);
            $data[$i]["positions"] = $this->positionReadRepository->getAllByIdsAndLang($this->unparsePositions($v["positions"]), $lang);
            unset($data[$i]["status_name"]);
        }
        return $data;
    }

    /**
     * Unparse positions
     * 
     * @param string $positions
     * 
     * @return array<int>
     */
    private function unparsePositions(string $positions) :array{
        $p = explode("@", $positions);
        $array = array();
        foreach ($p as $v) {
            if(is_numeric($v) && $v > 0) {
                $array[] = $v;
            }
        }
        return $array;
    }
}