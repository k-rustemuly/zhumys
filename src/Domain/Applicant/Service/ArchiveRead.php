<?php

namespace App\Domain\Applicant\Service;

use App\Domain\Applicant\Repository\ApplicantArchiveReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Text;
use App\Helper\Fields\Reference;
use App\Helper\Fields\Email;
use App\Helper\Fields\Tag;
use App\Domain\Position\Repository\PositionFinderRepository;

/**
 * Service.
 */
final class ArchiveRead {

    /**
     * @var ApplicantArchiveReadRepository
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
     * @param ApplicantArchiveReadRepository $readRepository
     * @param PositionFinderRepository $positionReadRepository
     *
     */
    public function __construct(ApplicantArchiveReadRepository $readRepository, PositionFinderRepository $positionReadRepository) {
        $this->readRepository = $readRepository;
        $this->positionReadRepository = $positionReadRepository;
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
    public function list(string $lang, array $params = array()) :array{
        $privilege_id = 0;
        if(isset($params["privilege_id"])) {
            $privilege_id = (int)$params["privilege_id"];
        }
        $companies = $this->readRepository->getAllBySearch($lang, $privilege_id);

        return $this->render
                ->lang($lang)
                ->header(self::getHeader())
                ->data($this->parseData($companies, $lang))
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
            "iin" => Field::getInstance()->init(new Number())->execute(),
            "full_name" => Field::getInstance()->init(new Text())->execute(),
            "status_id" => Field::getInstance()->init(new Reference())->reference_name("applicant-status") ->reference_id("id")->execute(),
            "positions" => Field::getInstance()->init(new Tag())->tag_name("position")->tag_id("id")->tag_show("name")->execute(),
            "email" => Field::getInstance()->init(new Email())->execute(),
            "privilege_id" => Field::getInstance()->init(new Reference())->reference_name("privilege")->reference_id("id")->execute(),
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
            $data[$i]["status_id"] = array("id" => $v["status_id"], "value" => $v["status_name"], "color" => $v["status_color"]);
            unset($data[$i]["status_name"]);
            unset($data[$i]["status_color"]);
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