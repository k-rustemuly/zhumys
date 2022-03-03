<?php

namespace App\Domain\Applicant\Service;

use App\Domain\Applicant\Repository\ApplicantReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Text;
use App\Helper\Fields\Date;
use App\Helper\Fields\PhoneNumber;
use App\Helper\Fields\Boolean;
use App\Helper\Fields\Textarea;
use App\Helper\Fields\Email;

/**
 * Service.
 */
final class Read{

    /**
     * @var ApplicantReadRepository
     */
    private $readRepository;

    /**
     * @var Render
     */
    private $render;

    /**
     * The constructor.
     * @param ApplicantReadRepository $readRepository
     *
     */
    public function __construct(ApplicantReadRepository $readRepository){
        $this->readRepository = $readRepository;
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
    public function list(string $lang) :array{
        $companies = $this->readRepository->getAll();

        return $this->render
                ->lang($lang)
                ->header(self::getHeader())
                ->data($companies)
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
            "raiting_number" => Field::getInstance()->init(new Number())->execute(),
            "iin" => Field::getInstance()->init(new Number())->can_create(true)->is_required(true)->min_length(12)->max_length(12)->execute(),
            "full_name" => Field::getInstance()->init(new Text())->can_create(true)->can_update(true)->is_required(true)->min_length(3)->execute(),
            "birthdate" => Field::getInstance()->init(new Date())->can_create(true)->is_required(true)->min_date("1900-01-01")->max_date(date("Y-m-d"))->execute(),
            "email" => Field::getInstance()->init(new Email())->can_update(true)->execute(),
            "phone_number" => Field::getInstance()->init(new PhoneNumber())->is_required(true)->can_create(true)->can_update(true)->execute(),
            "address" => Field::getInstance()->init(new Textarea())->can_create(true)->can_update(true)->is_required(true)->execute(),
            "second_phone_number" => Field::getInstance()->init(new Textarea())->can_create(true)->can_update(true)->execute(),
            "is_have_whatsapp" => Field::getInstance()->init(new Boolean())->execute(),
            "is_have_telegram" => Field::getInstance()->init(new Boolean())->execute(),
            "comment" => Field::getInstance()->init(new Textarea())->can_create(true)->can_update(true)->execute(),
        );
    }
}