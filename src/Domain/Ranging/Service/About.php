<?php

namespace App\Domain\Ranging\Service;

use App\Domain\Ranging\Repository\RangingReaderRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Date;
use App\Helper\Fields\Reference;
use App\Helper\Fields\Email;
use App\Domain\Company\Admin;
use App\Helper\Fields\Text;
use App\Helper\Fields\PhoneNumber;
use App\Helper\Fields\Time;
use DomainException;

/**
 * Service.
 */
final class About extends Admin{

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
     * The constructor.
     * @param RangingReaderRepository $rangingReadRepository
     *
     */
    public function __construct(RangingReaderRepository $rangingReadRepository) {
        $this->rangingReadRepository = $rangingReadRepository;
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
    public function get(string $lang, int $freePlaceId, int $rangingId) :array{
        $this->info = $this->rangingReadRepository->findByIdAndFreePlaceIdAndBinAndLang($rangingId, $freePlaceId, $this->getBin(), $lang);
        if(empty($this->info)) throw new DomainException("Free place not found");
        $render = $this->render
                ->lang($lang)
                ->block("candidate_info", $this->getCandidateBlockValues());
        return $render->build();
    }

    /**
     * Get free place info block values
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
            "reason" => Field::getInstance()->init(new Text())->value($this->info["reason"])->execute()
        );
    }

}