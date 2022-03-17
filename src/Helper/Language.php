<?php
namespace App\Helper;

class Language{
    public $string = [];
    public $substring = [];

    public function locale(string $lang = "ru"):self{
        $this->string = json_decode(file_get_contents(TRANSLATE_DIR.$lang.'.json'), true);
        return $this;
    }

    public function get(string $key = ""){
        $this->substring = isset($this->string[$key]) ? $this->string[$key] : [];
        return isset($this->string[$key]) ? $this->string[$key] : $key;
    }

    public function getSub(string $key = ""){
        return isset($this->substring[$key]) ? $this->substring[$key] : $key;
    }
}
?>