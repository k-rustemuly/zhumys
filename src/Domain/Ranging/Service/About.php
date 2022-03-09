<?php

namespace App\Domain\Ranging\Service;

use App\Domain\Ranging\Repository\RangingReaderRepository;
use App\Helper\Field;
use App\Helper\Render;
use App\Helper\Fields\Number;
use App\Helper\Fields\Textarea;
use App\Helper\Fields\Reference;
use App\Helper\Fields\DateTime;
use App\Domain\Company\Admin;
use App\Helper\Fields\Text;
use DomainException;

/**
 * Service.
 */
final class About extends Admin{

    /**
     * @var Render
     */
    private $render;

    /**
     * @var array
     */
    private $info;

    /**
     * @var RangingReaderRepository
     */
    private $rangingReadRepository;

    /**
     * The constructor.
     * @param RangingReaderRepository $rangingReadRepository
     *
     */
    public function __construct(RangingReaderRepository $rangingReadRepository) {
        $this->rangingReadRepository = $rangingReadRepository;
        $this->render = new Render();
    }

    /**
     * Get about one free place
     * 
     * @param int $id The id
     * @param string $lang The interface language code
     *
     * @return array<mixed> $post fileds The post fields
     * 
     */
    public function get(string $lang, int $freePlaceId, int $rangingId) :array{
        $this->info = $this->rangingReadRepository->findByIdAndFreePlaceIdAndBinAndLang($rangingId, $freePlaceId, $this->getBin(), $lang);
        if(empty($this->info)) throw new DomainException("Free place not found");
        $render = $this->render
                ->lang($lang)
                ->block("candidate_info", $this->getCandidateBlockValues());
        return $render->build();
    }

    /**
     * Get free place info block values
     *
     * @param array<mixed> $values
     * 
     */
    public function getCandidateBlockValues() :array{
        return array(
            "id" => Field::getInstance()->init(new Number())->value($this->info["id"])->execute(),
            "iin" => Field::getInstance()->init(new Number())->value($this->info["iin"])->execute(),
        );
    }

}