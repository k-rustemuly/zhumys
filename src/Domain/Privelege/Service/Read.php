<?php

namespace App\Domain\Privelege\Service;

use App\Domain\Privelege\Repository\PrivelegeReadRepository;
use DomainException;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Text;
use App\Helper\Fields\Number;

/**
 * Service.
 */
final class Read {
    /**
     * @var PrivelegeReadRepository
     */
    private $repository;

    /**
     * @var Render
     */
    private $render;

    /**
     * The constructor.
     *
     * @param PrivelegeReadRepository $repository The repository
     */
    public function __construct(PrivelegeReadRepository $repository) {
        $this->repository = $repository;
        $this->render = new Render();
    }

    /**
     * Get handbook.
     *
     * @param string $lang interface language
     *
     * @throws DomainException
     * 
     * @return array<mixed> The result
     */
    public function get(string $lang) :array{
        return $this->repository->getAllByLang($lang);
    }
    /**
     * Get list
     * 
     * @return array<mixed> The result
     */
    public function list(string $lang) :array{
        $list = $this->repository->getAll();
        return $this->render
                ->lang($lang)
                ->header(self::getHeader())
                ->data($list)
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
            "id" => Field::getInstance()->init(new Number())->execute(),
            "name_kk" => Field::getInstance()->init(new Text())->can_create(true)->can_update(true)->is_required(true)->min_length(3)->execute(),
            "name_ru" => Field::getInstance()->init(new Text())->can_create(true)->can_update(true)->is_required(true)->min_length(3)->execute(),
        );
    }
}
