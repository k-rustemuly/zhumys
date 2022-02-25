<?php 
declare(strict_types=1);

namespace App\Helper;

class Config{

    /**
     * @var array<mixed>
     */
    private $array;

    /**
     * @var Language
     */
    private $language;

    /**
     * Добавляем язык
     *
     * @param string       $lang
     *
     */
    public function setLang(string $lang) :self{
        $this->language = new Language();
        $this->language = $this->language->locale($lang);
        $this->lang = $lang;
        return $this;
    }
}