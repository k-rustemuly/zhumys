<?php

namespace App\Domain\Center\Company\Service;

use App\Domain\Company\Repository\CompanyReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Text;
use App\Helper\Fields\Boolean;
use DomainException;

/**
 * Service.
 */
final class InfoRead{

    /**
     * @var CompanyReadRepository
     */
    private $readRepository;

    /**
     * @var Render
     */
    private $render;

    /**
     * @var array
     */
    private $companyInfo;

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
     * @param int $id The company id
     * @param string $lang The interface language code
     * 
     * @throws DomainException
     *
     * @return array<mixed> $post fileds The post fields
     * 
     */
    public function info(int $id, string $lang) :array{
        $companyInfo = $this->readRepository->getById($id);
        if(empty($companyInfo)) throw new DomainException("Company not found");
        $this->companyInfo = $companyInfo;
        return $this->render
                ->lang($lang)
                ->block("company_info", $this->getCompanyInfoBlockValues())
                ->build();
    }

    /**
     * Get company info block values
     *
     * @param array<mixed> $values
     * 
     */
    public function getCompanyInfoBlockValues() :array{
        return array(
            "bin" => Field::getInstance()->init(new Number())->value($this->companyInfo["bin"])->execute(),
            "name_kk" => Field::getInstance()->init(new Text())->value($this->companyInfo["name_kk"])->execute(),
            "name_ru" => Field::getInstance()->init(new Text())->value($this->companyInfo["name_ru"])->execute(),
            "full_name_kk" => Field::getInstance()->init(new Text())->value($this->companyInfo["full_name_kk"])->execute(),
            "full_name_ru" => Field::getInstance()->init(new Text())->value($this->companyInfo["full_name_ru"])->execute(),
            "director_fullname" => Field::getInstance()->init(new Text())->value($this->companyInfo["director_fullname"])->execute(),
            "is_active" => Field::getInstance()->init(new Boolean())->value($this->companyInfo["is_active"])->execute(),
        );
    }
}