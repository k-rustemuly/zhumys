<?php

namespace App\Domain\Center\Company\Service;

use App\Domain\Company\Repository\CompanyReadRepository;
use DomainException;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Text;
use App\Helper\Fields\Boolean;

/**
 * Service.
 */
final class Read{

    /**
     * @var CompanyReadRepository
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
    public function __construct(CompanyReadRepository $readRepository){
        $this->readRepository = $readRepository;
        $this->render = new Render();
    }

    /**
     * Get company info
     *
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     */
    public function list(string $lang) :array{
        $render = $this->render;
        $render->setLang($lang);
        $render->setHeaders(array(
                                "bin" => Field::getInstance()->init(new Number())->can_change(true)->required(true)->min_length(12)->max_length(12)->execute(),
                                "name_kk" => Field::getInstance()->init(new Text())->can_change(true)->required(true)->min_length(3)->execute(),
                                "name_ru" => Field::getInstance()->init(new Text())->can_change(true)->required(true)->min_length(3)->execute(),
                                "full_name_kk" => Field::getInstance()->init(new Text())->execute(),
                                "full_name_ru" => Field::getInstance()->init(new Text())->execute(),
                                "oked_code" => Field::getInstance()->init(new Number())->execute(),
                                "krp_code" => Field::getInstance()->init(new Number())->execute(),
                                "kato_code" => Field::getInstance()->init(new Number())->execute(),
                                "full_address_kk" => Field::getInstance()->init(new Text())->execute(),
                                "full_address_ru" => Field::getInstance()->init(new Text())->execute(),
                                "is_ip" => Field::getInstance()->init(new Boolean())->execute(),
                            )
        );
        $companies = $this->readRepository->getAll();
        $render->setDatas($companies);
        return $render->build();
    }
}