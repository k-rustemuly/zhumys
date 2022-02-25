<?php 
declare(strict_types=1);

namespace App\Helper;

class Render{

    /**
     * @var array<mixed>
     */
    private $array = array();

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
    public function lang(string $lang) :self{
        $this->language = new Language();
        $this->language = $this->language->locale($lang);
        $this->lang = $lang;
        return $this;
    }

    public function header(array $array) :self{
        $this->array["header"] = $array;
        return $this;
    }

    public function data(array $array) :self{
        $this->array["data"] = $array;
        return $this;
    }

    public function build() :array{
        foreach($this->array["header"] as $key => $value){
            $field = $this->language->get("field")[$key];
            $hint = $this->language->get("hint")[$key];
            $this->array["header"][$key]["name"] = $field;
            $this->array["header"][$key]["hint"] = $hint;
        }
        return $this->array;
    }
}