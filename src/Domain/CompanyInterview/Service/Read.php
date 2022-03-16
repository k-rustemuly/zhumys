<?php

namespace App\Domain\CompanyInterview\Service;

use App\Domain\Ranging\Repository\RangingReaderRepository as ReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Text;
use App\Helper\Fields\Date;
use App\Helper\Fields\PhoneNumber;
use App\Helper\Fields\Reference;
use App\Helper\Fields\Tag;
use App\Helper\Fields\Time;
use App\Domain\Position\Repository\PositionFinderRepository;
use App\Domain\Company\Admin;

/**
 * Service.
 */
final class Read extends Admin{

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
        $limit = 0;
        $orderAsc = array();
        if(isset($params["orderAsc"])) {
            $o = explode(',', $params["orderAsc"]);
            foreach($o as $field) {
                if(strlen($field)>0) {
                    $orderAsc[] = $field;
                }
            }
        }
        $orderDesc = array();
        if(isset($params["orderDesc"])) {
            $o = explode(',', $params["orderDesc"]);
            foreach($o as $field) {
                if(strlen($field)>0) {
                    $orderDesc[] = $field;
                }
            }
        }
        if(isset($params["limit"]) && $params["limit"] > 0) {
            $limit = $params["limit"];
        }
        $list = $this->readRepository->search($lang, $this->getBin(), 2, $limit, $orderAsc, $orderDesc);

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
            "position_id" => Field::getInstance()->init(new Reference())->reference_name("position")->reference_id("id")->execute(),
            "interview_date" => Field::getInstance()->init(new Date())->execute(),
            "interview_time" => Field::getInstance()->init(new Time())->execute(),
            //"birthdate" => Field::getInstance()->init(new Date())->execute(),
            //"email" => Field::getInstance()->init(new Email())->is_visible(false)->execute(),
            "phone_number" => Field::getInstance()->init(new PhoneNumber())->execute(),
            //"address" => Field::getInstance()->init(new Textarea())->is_visible(false)->execute(),
            //"second_phone_number" => Field::getInstance()->init(new Textarea())->is_visible(false)->execute(),
            "privilege_id" => Field::getInstance()->init(new Reference())->reference_name("privilege")->reference_id("id")->execute(),
            //"status_id" => Field::getInstance()->init(new Reference())->reference_name("applicant-status")->reference_id("id")->execute(),
            //"positions" => Field::getInstance()->init(new Tag())->tag_name("position")->tag_id("id")->tag_show("name")->is_visible(true)->can_create(true)->can_update(true)->is_required(true)->execute(),
            // "is_have_whatsapp" => Field::getInstance()->init(new Boolean())->execute(),
            // "is_have_telegram" => Field::getInstance()->init(new Boolean())->execute(),
            //"comment" => Field::getInstance()->init(new Textarea())->is_visible(false)->can_create(true)->can_update(true)->execute(),
            //"created_at" => Field::getInstance()->init(new DateTime())->execute(),
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
            $data[$i]["position_id"] = array("id" => $v["position_id"], "value" => $v["position_name"]);
            unset($data[$i]["position_name"]);
        }
        return $data;
    }
}