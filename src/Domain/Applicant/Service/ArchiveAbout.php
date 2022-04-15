<?php

namespace App\Domain\Applicant\Service;

use App\Domain\Applicant\Repository\ApplicantArchiveReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Text;
use App\Helper\Fields\Date;
use App\Helper\Fields\DateTime;
use App\Helper\Fields\PhoneNumber;
use App\Helper\Fields\Reference;
use App\Helper\Fields\Textarea;
use App\Helper\Fields\Email;
use App\Helper\Fields\Tag;
use App\Domain\Position\Repository\PositionFinderRepository;
use App\Domain\Ranging\Log\Repository\RangingLogReadRepository;
use DomainException;

/**
 * Service.
 */
final class ArchiveAbout {

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
     * @var array
     */
    private $info;

    /**
     * @var RangingLogReadRepository
     */
    private $logReadRepository;

    /**
     * The constructor.
     * @param ApplicantArchiveReadRepository $readRepository
     * @param PositionFinderRepository $positionReadRepository
     * @param RangingLogReadRepository $logReadRepository
     *
     */
    public function __construct(ApplicantArchiveReadRepository $readRepository,
                                PositionFinderRepository $positionReadRepository,
                                RangingLogReadRepository $logReadRepository) {
        $this->readRepository = $readRepository;
        $this->positionReadRepository = $positionReadRepository;
        $this->logReadRepository = $logReadRepository;
        $this->render = new Render();
    }

    /**
     * Get applicant list
     * 
     * @param int $id Applicant id
     * @param string $lang The interface language code
     *
     * @return array<mixed> $post fileds The post fields
     * 
     */
    public function about(int $id, string $lang) :array{
        $this->info = $this->readRepository->findByIdAndLang($id, $lang);
        if(empty($this->info)) {
            throw new DomainException("Applicant not found");
        } 
        return $this->render
                ->lang($lang)
                ->block("applicant_info", $this->getApplicantBlockValues($lang))
                //->block("applicant_log_info", $this->getCandidateLogBlockValues($this->logReadRepository->getAllByApplicantIdAndLang($id, $lang)))
                ->build();
    }

    /**
     * Get header
     * 
     * @param string $lang
     * 
     * @return array<mixed>
     * 
     */
    public function getApplicantBlockValues(string $lang) :array{
        return array(
            "full_name" => Field::getInstance()->init(new Text())->can_update(true)->is_required(true)->min_length(3)->value($this->info["full_name"])->execute(),
            "iin" => Field::getInstance()->init(new Number())->value($this->info["iin"])->execute(),
            "status_id" => Field::getInstance()->init(new Reference())->reference_name("applicant-status")->reference_id("id")->value(array("id" => $this->info["status_id"], "value" => $this->info["status_name"], "color" => $this->info["status_color"]))->execute(),
            "birthdate" => Field::getInstance()->init(new Date())->can_create(true)->is_required(true)->min_date("1900-01-01")->max_date(date("Y-m-d"))->value($this->info["birthdate"])->execute(),
            "positions" => Field::getInstance()->init(new Tag())->tag_name("position")->tag_id("id")->tag_show("name")->is_visible(true)->can_create(true)->can_update(true)->is_required(true)->value($this->positionReadRepository->getAllByIdsAndLang($this->unparsePositions($this->info["positions"]), $lang))->execute(),
            "privilege_id" => Field::getInstance()->init(new Reference())->reference_name("privilege")->reference_id("id")->can_create(true)->can_update(true)->is_required(true)->value(array("id" => $this->info["privilege_id"], "value" => $this->info["privilege_name"]))->execute(),
            "email" => Field::getInstance()->init(new Email())->can_create(true)->can_update(true)->is_required(true)->value($this->info["email"])->execute(),
            "phone_number" => Field::getInstance()->init(new PhoneNumber())->is_required(true)->can_create(true)->can_update(true)->value($this->info["phone_number"])->execute(),
            "address" => Field::getInstance()->init(new Textarea())->can_create(true)->can_update(true)->is_required(true)->value($this->info["address"])->execute(),
            "second_phone_number" => Field::getInstance()->init(new Textarea())->can_create(true)->can_update(true)->value($this->info["second_phone_number"])->execute(),
            "raiting_number" => Field::getInstance()->init(new Number())->value($this->info["raiting_number"])->execute(),
            "comment" => Field::getInstance()->init(new Textarea())->can_create(true)->can_update(true)->value($this->info["comment"])->execute(),
        );
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

    /**
     * Get ranging candidate log info block values
     *
     * @param array<mixed> $values
     * 
     */
    public function getCandidateLogBlockValues(array $data) :array{
        $array = array();
        foreach ($data as $i => $v){
            $array[$i] = array(
                "status_id" => Field::getInstance()->init(new Reference())->reference_name("ranging-status")->reference_id("id")->value(array("id" => $v["status_id"], "value" => $v["status_name"], "color" => $v["status_color"]))->execute(),
                "admin_full_name" => Field::getInstance()->init(new Text())->value($v["admin_full_name"])->execute(),
                "company_name" => Field::getInstance()->init(new Text())->value($v["company_name"])->execute(),
                "reason" => Field::getInstance()->init(new Text())->value($v["reason"])->execute(),
                "created_at" => Field::getInstance()->init(new DateTime())->value($v["created_at"])->execute(),
            );
        }
        return $array;
    }
}