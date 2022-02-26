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

    public function block(string $key, array $array) :self{
        $this->array["block"][$key] = array(
            "name" => null,
            "values" => $array
        );
        return $this;
    }

    public function build() :array{
        if(isset($this->array["header"]))
            foreach($this->array["header"] as $key => $value){
                $field = $this->language->get("field")[$key];
                $hint = $this->language->get("hint")[$key];
                $this->array["header"][$key]["name"] = $field;
                $this->array["header"][$key]["hint"] = $hint;
            }
        if(isset($this->array["block"]))
            foreach($this->array["block"] as $key => $value){
                $block = $this->language->get("block")[$key];
                $this->array["block"][$key]["name"] = $block;
            }
        return $this->array;
    }
}