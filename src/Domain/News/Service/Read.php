<?php

namespace App\Domain\News\Service;

use App\Domain\News\Repository\NewsReadRepository as ReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Image;
use App\Helper\Fields\Text;
use App\Helper\Fields\Reference;
use App\Helper\Fields\Number;
use App\Helper\Fields\DateTime;
use App\Helper\Fields\Boolean;
use App\Helper\Fields\Textarea;
use App\Domain\Center\Admin;

/**
 * Service.
 */
final class Read extends Admin {

    /**
     * @var ReadRepository
     */
    private $readRepository;

    /**
     * @var Render
     */
    private $render;

    /**
     * The constructor.
     * @param ReadRepository $readRepository
     *
     */
    public function __construct(ReadRepository $readRepository) {
        $this->readRepository = $readRepository;
        $this->render = new Render();
    }

    /**
     * Get news list
     * 
     * @param string $lang The interface language code
     * @param array<mixed> $params The get params
     *
     * @return array<mixed> 
     * 
     */
    public function list(string $lang, array $params) :array{

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
        $list = $this->readRepository->search($lang, $this->getOrgBin(), $limit, $orderAsc, $orderDesc);

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
            "id" => Field::getInstance()->init(new Number())->is_visible(false)->execute(),
            "title" => Field::getInstance()->init(new Text())->can_create(true)->can_update(true)->is_required(true)->execute(),
            "lang" => Field::getInstance()->init(new Reference())->can_create(true)->can_update(true)->is_required(true)->reference_name("language")->reference_id("id")->execute(),
            "is_public" => Field::getInstance()->init(new Boolean())->execute(),
            "created_at" => Field::getInstance()->init(new DateTime())->execute(),
            "image" => Field::getInstance()->init(new Image())->can_create(true)->can_update(true)->execute(),
            "anons" => Field::getInstance()->init(new Text())->can_create(true)->can_update(true)->is_required(true)->execute(),
            "body" => Field::getInstance()->init(new Textarea())->can_create(true)->can_update(true)->is_required(true)->execute(),
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
        $this->language->locale($lang);
        foreach ($data as $i => $v) {
            $data[$i]["image"] = $_ENV["API_URL"] . $v["image"];
            $data[$i]["is_public"] = array("id" => $v["is_public"], "value" => $this->language->get("boolean")["is_public"][$v["is_public"]]);
            $data[$i]["lang"] = array("id" => $v["lang"], "value" => $data[$i]["language_name"]);
            unset($data[$i]["language_name"]);
        }
        return $data;
    }
}