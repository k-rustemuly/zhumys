<?php

namespace App\Domain\CompanyInfo\Service;

use App\Domain\Company\Repository\CompanyReadRepository as ReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Text;
use App\Domain\Company\Admin;

/**
 * Service.
 */
final class Read extends Admin {

    /**
     * @var ReadRepository
     */
    private $readRepository;

    /**
     * @var Render
     */
    private $render;

    /**
     * The constructor.
     * @param ReadRepository $readRepository
     *
     */
    public function __construct(ReadRepository $readRepository) {
        $this->readRepository = $readRepository;
        $this->render = new Render();
    }

    /**
     * Get list
     * 
     * @param string $lang The interface language code
     *
     * @return array<mixed> $post fileds The post fields
     * 
     */
    public function list(string $lang) :array{
        
        $list = $this->readRepository->getByBin($this->getBin());

        return $this->render
                ->lang($lang)
                ->block("company_profile_info", $this->getHeader($list))
                ->build();
    }

    /**
     * Get header
     *
     * @param array<mixed> $header
     * 
     */
    public static function getHeader(array $data) :array{
        return array(
            "name_kk" => Field::getInstance()->init(new Text())->value($data["name_kk"])->execute(),
            "name_ru" => Field::getInstance()->init(new Text())->value($data["name_ru"])->execute(),
            "full_name_kk" => Field::getInstance()->init(new Text())->can_update(true)->value($data["full_name_kk"])->execute(),
            "full_name_ru" => Field::getInstance()->init(new Text())->can_update(true)->value($data["full_name_ru"])->execute(),
            "full_address_kk" => Field::getInstance()->init(new Text())->can_update(true)->value($data["full_address_kk"])->execute(),
            "full_address_ru" => Field::getInstance()->init(new Text())->can_update(true)->value($data["full_address_ru"])->execute(),
            "phone_number" => Field::getInstance()->init(new Text())->can_update(true)->value($data["phone_number"])->execute(),
            "director_fullname" => Field::getInstance()->init(new Text())->can_update(true)->value($data["director_fullname"])->execute(),
        );
    }
}