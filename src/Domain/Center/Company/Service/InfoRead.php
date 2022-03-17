<?php

namespace App\Domain\Center\Company\Service;

use App\Domain\Company\Repository\CompanyReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Text;
use App\Helper\Fields\Boolean;
use App\Helper\Language;
use DomainException;

/**
 * Service.
 */
final class InfoRead {

    /**
     * @var CompanyReadRepository
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
     * @var array
     */
    private $companyInfo;

    /**
     * The constructor.
     * @param CompanyReadRepository $readRepository
     *
     */
    public function __construct(CompanyReadRepository $readRepository) {
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
     * @throws DomainException
     *
     * @return array<mixed> $post fileds The post fields
     * 
     */
    public function info(string $bin, string $lang) :array{
        $this->language->locale($lang);
        $companyInfo = $this->readRepository->getByBin($bin);
        if(empty($companyInfo)) {
            throw new DomainException("Company not found");
        }
        $this->companyInfo = $companyInfo;
        return $this->render
                ->lang($lang)
                ->block("company_info", $this->getCompanyInfoBlockValues())
                ->block("company_admins", array())
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
            "name_kk" => Field::getInstance()->init(new Text())->value($this->companyInfo["name_kk"])->can_update(true)->execute(),
            "name_ru" => Field::getInstance()->init(new Text())->value($this->companyInfo["name_ru"])->can_update(true)->execute(),
            "full_name_kk" => Field::getInstance()->init(new Text())->value($this->companyInfo["full_name_kk"])->execute(),
            "full_name_ru" => Field::getInstance()->init(new Text())->value($this->companyInfo["full_name_ru"])->execute(),
            "director_fullname" => Field::getInstance()->init(new Text())->value($this->companyInfo["director_fullname"])->execute(),
            "is_active" => Field::getInstance()->init(new Boolean())->value(array("id" => $this->companyInfo["is_active"], "value" => $this->language->get("boolean")["company_is_active"][$this->companyInfo["is_active"]]))->can_update(true)->execute(),
        );
    }
}