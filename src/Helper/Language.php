<?php
namespace App\Helper;

class Language{
    public $string = [];

    public function locale(string $lang = "ru"):self{
        $this->string = json_decode(file_get_contents(TRANSLATE_DIR.$lang.'.json'), true);
        return $this;
    }

    public function get(string $key = "", string $default = "<Error>The word is not isset<Error>"){
        return isset($this->string[$key]) ? $this->string[$key] : $default;
    }
}
?>