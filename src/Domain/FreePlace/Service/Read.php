<?php

namespace App\Domain\FreePlace\Service;

use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Text;
use App\Helper\Fields\Reference;
use App\Helper\Fields\DateTime;
use App\Helper\Fields\File;
use App\Helper\Fields\Password;
use App\Domain\Company\Admin;

/**
 * Service.
 */
final class Read extends Admin{

    /**
     * @var FreePlaceReadRepository
     */
    private $readRepository;

    /**
     * @var Render
     */
    private $render;

    /**
     * The constructor.
     * @param FreePlaceReadRepository $readRepository
     *
     */
    public function __construct(FreePlaceReadRepository $readRepository){
        $this->readRepository = $readRepository;
        $this->render = new Render();
    }

    /**
     * Get applicant list
     * 
     * @param string $lang The interface language code
     * @param array<mixed> $params The get params
     *
     * @return array<mixed> $post fileds The post fields
     * 
     */
    public function list(string $lang, array $params) :array{
        $data = $this->readRepository->getAllByBin($this->getBin());

        return $this->render
                ->lang($lang)
                ->header(self::getHeader())
                ->data($data)
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
            "position_id" => Field::getInstance()->init(new Reference())->can_create(true)->reference_name("position")->reference_id("id")->is_required(true)->execute(),
            "count" => Field::getInstance()->init(new Number())->can_create(true)->is_required(true)->min(1)->execute(),
            "comment" => Field::getInstance()->init(new Text())->can_create(true)->is_visible(false)->execute(),
            "status_id" => Field::getInstance()->init(new Reference())->reference_name("place-status")->reference_id("id")->execute(),
            "created_at" => Field::getInstance()->init(new DateTime())->execute(),
            "sign_p12" => Field::getInstance()->init(new File())->is_visible(false)->can_create(true)->is_required(true)->execute(),
            "password" => Field::getInstance()->init(new Password())->is_visible(false)->can_create(true)->is_required(true)->execute(),
        );
    }
}