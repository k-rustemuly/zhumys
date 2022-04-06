<?php

namespace App\Domain\Ranging\Service;

use App\Domain\Ranging\Repository\RangingReaderRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Date;
use App\Helper\Fields\Reference;
use App\Helper\Fields\Email;
use App\Helper\Fields\Text;
use App\Helper\Fields\PhoneNumber;
use App\Helper\Fields\Time;
use App\Helper\Fields\DateTime;
use DomainException;
use App\Domain\Company\Admin;
use App\Domain\Ranging\Log\Repository\RangingLogReadRepository;

/**
 * Service.
 */
final class About extends Admin {

    /**
     * @var Render
     */
    private $render;

    /**
     * @var array
     */
    private $info;

    /**
     * @var RangingReaderRepository
     */
    private $rangingReadRepository;

    /**
     * @var RangingLogReadRepository
     */
    private $logReadRepository;

    /**
     * The constructor.
     * @param RangingReaderRepository $rangingReadRepository
     * @param RangingLogReadRepository $logReadRepository
     *
     */
    public function __construct(RangingReaderRepository $rangingReadRepository, RangingLogReadRepository $logReadRepository) {
        $this->rangingReadRepository = $rangingReadRepository;
        $this->logReadRepository = $logReadRepository;
        $this->render = new Render();
    }

    /**
     * Get about one ranging candidate
     * 
     * @param string $lang The interface language code
     * @param int $rangingId The id
     *
     * @return array<mixed> $post fileds The post fields
     * 
     */
    public function get(string $lang, int $rangingId) :array{
        $this->info = $this->rangingReadRepository->findByIdAndBinAndLang($rangingId, $this->getBin(), $lang);
        if(empty($this->info)) throw new DomainException("Free place not found");
        $render = $this->render
                ->lang($lang)
                ->block("candidate_info", $this->getCandidateBlockValues())
                ->block("candidate_log_info", $this->getCandidateLogBlockValues($this->logReadRepository->getAllByIdAndLang($rangingId, $lang)));
        return $render->build();
    }

    /**
     * Get ranging candidate info block values
     *
     * @param array<mixed> $values
     * 
     */
    public function getCandidateBlockValues() :array{
        return array(
            "id" => Field::getInstance()->init(new Number())->value($this->info["id"])->execute(),
            "raiting_number" => Field::getInstance()->init(new Number())->value($this->info["raiting_number"])->execute(),
            "iin" => Field::getInstance()->init(new Number())->value($this->info["iin"])->execute(),
            "full_name" => Field::getInstance()->init(new Text())->value($this->info["full_name"])->execute(),
            "birthdate" => Field::getInstance()->init(new Date())->value($this->info["birthdate"])->execute(),
            "privilege_id" => Field::getInstance()->init(new Reference())->reference_name("privilege")->reference_id("id")->value(array("id" => $this->info["privilege_id"], "value" => $this->info["privilege_name"]))->execute(),
            "email" => Field::getInstance()->init(new Email())->value($this->info["email"])->execute(),
            "phone_number" => Field::getInstance()->init(new PhoneNumber())->value($this->info["phone_number"])->execute(),
            "address" => Field::getInstance()->init(new Text())->value($this->info["address"])->execute(),
            "second_phone_number" => Field::getInstance()->init(new Text())->value($this->info["second_phone_number"])->execute(),
            "comment" => Field::getInstance()->init(new Text())->value($this->info["comment"])->execute(),
            "status_id" => Field::getInstance()->init(new Reference())->reference_name("ranging-status-status")->reference_id("id")->value(array("id" => $this->info["status_id"], "value" => $this->info["status_name"], "color" => $this->info["status_color"]))->execute(),
            "interview_date" => Field::getInstance()->init(new Date())->value($this->info["interview_date"])->execute(),
            "interview_time" => Field::getInstance()->init(new Time())->value($this->info["interview_time"])->execute(),
            "interview_comment" => Field::getInstance()->init(new Text())->value($this->info["interview_comment"])->execute(),
            "reason" => Field::getInstance()->init(new Text())->value($this->info["reason"])->execute(),
            "order_no" => Field::getInstance()->init(new Text())->value($this->info["order_no"])->execute(),
            "order_date" => Field::getInstance()->init(new Date())->value($this->info["order_date"])->execute()
        );
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