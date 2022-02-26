<?php

namespace App\Domain\Admin\Company\Service;

use App\Domain\Admin\Company\Repository\AdminReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Text;
use App\Helper\Fields\Boolean;
use App\Helper\Fields\Date;

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
     * The constructor.
     * @param CompanyReadRepository $readRepository
     *
     */
    public function __construct(AdminReadRepository $readRepository){
        $this->readRepository = $readRepository;
        $this->render = new Render();
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
        $companies = $this->readRepository->getByBin($bin);

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
            "iin" => Field::getInstance()->init(new Number())->required(true)->min_length(12)->max_length(12)->execute(),
            "surname" => Field::getInstance()->init(new Text())->can_change(true)->required(true)->min_length(2)->execute(),
            "name" => Field::getInstance()->init(new Text())->can_change(true)->required(true)->min_length(2)->execute(),
            "lastname" => Field::getInstance()->init(new Text())->can_change(true)->execute(),
            "birthdate" => Field::getInstance()->init(new Date())->execute(),
            "email" => Field::getInstance()->init(new Text())->execute(),
            "is_active" => Field::getInstance()->init(new Boolean())->can_change(true)->execute(),
        );
    }
}