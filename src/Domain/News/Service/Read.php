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

        $data = $this->readRepository->getAllByBinAndLang($this->getOrgBin(), $lang);

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
            "id" => Field::getInstance()->init(new Number())->execute(),
            "lang" => Field::getInstance()->init(new Reference())->reference_name("language")->reference_id("id")->can_create(true)->can_update(true)->is_visible(false)->execute(),
            "image" => Field::getInstance()->init(new Image())->can_create(true)->can_update(true)->execute(),
            "title" => Field::getInstance()->init(new Text())->can_create(true)->can_update(true)->execute(),
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
    private function parseData(array $data) :array{
        foreach($data as $i => $v) {
            foreach($v as $key => $val) {
                switch($key) {
                    case "lang":
                        $data[$i][$key] = array("id" => $val, "value" => $data[$i]["lang_name"]);
                        unset($data[$i]["lang_name"]);
                    break;
                }
            }
        }
        return $data;
    }
}