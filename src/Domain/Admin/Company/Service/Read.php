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
            "iin" => Field::getInstance()->init(new Number())->can_create(true)->is_required(true)->min_length(12)->max_length(12)->execute(),
            "full_name" => Field::getInstance()->init(new Text())->can_create(true)->can_update(true)->is_required(true)->min_length(2)->execute(),
            "birthdate" => Field::getInstance()->init(new Date())->execute(),
            "email" => Field::getInstance()->init(new Email())->can_create(true)->execute(),
            "is_active" => Field::getInstance()->init(new Boolean())->can_update(true)->execute(),
        );
    }
}