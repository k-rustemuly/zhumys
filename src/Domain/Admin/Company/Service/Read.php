<?php

namespace App\Domain\Admin\Company\Service;

use App\Domain\Admin\Company\Repository\AdminReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Text;
use App\Helper\Fields\Boolean;
use App\Helper\Fields\Date;
use App\Helper\Fields\Email;
use App\Helper\Language;

/**
 * Service.
 */
final class Read{

    /**
     * @var AdminReadRepository
     */
    private $readRepository;

    /**
     * @var Render
     */
    private $render;

    /**
     * @var Language
     */
    private $language;

    /**
     * The constructor.
     * @param CompanyReadRepository $readRepository
     *
     */
    public function __construct(AdminReadRepository $readRepository){
        $this->readRepository = $readRepository;
        $this->render = new Render();
        $this->language = new Language();
    }

    /**
     * Get company info
     * 
     * @param string $bin The company bin
     * @param string $lang The interface language code
     *
     * @return array<mixed> $post fileds The post fields
     * 
     */
    public function list(string $bin,string $lang) :array{
        $this->language->locale($bin);
        $companies = $this->readRepository->getByBin($bin);

        return $this->render
                ->lang($lang)
                ->header(self::getHeader())
                ->data($this->parseData($companies))
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
            "iin" => Field::getInstance()->init(new Number())->can_create(true)->is_required(true)->min_length(12)->max_length(12)->execute(),
            "full_name" => Field::getInstance()->init(new Text())->can_create(true)->can_update(true)->is_required(true)->min_length(2)->execute(),
            "birthdate" => Field::getInstance()->init(new Date())->execute(),
            "email" => Field::getInstance()->init(new Email())->can_create(true)->execute(),
            "is_active" => Field::getInstance()->init(new Boolean())->can_update(true)->execute(),
        );
    }

    /**
     * Parse data
     *
     * @param array<mixed> $header
     * 
     */
    private function parseData(array $data) :array{
        foreach ($data as $i => $d) {
            $data[$i]["is_active"] = array(
                "id" => $d["is_active"],
                "value" => $this->language->get("boolean")["company_is_active"][$d["is_active"]]
            );
        }
        return $data;
    }
}