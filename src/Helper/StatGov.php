<?php
declare(strict_types=1);

namespace App\Helper;

class StatGov{

    /**
     * Full url to api
     *
     * @var string
     */
    protected $url;

    /**
     * List of languages for parse datas
     *
     * @var array
     */
    protected $languages;

    public function __construct(string $url, array $languages){
        $this->url = $url;
        $this->languages = $languages;
    }

    /**
     * 
     * @return mixed $result
     *
     */
    public function getInfo(string $bin):array{
        $info = array(
            "bin" => $bin,
            "oked_code" => null,
            "krp_code" => null,
            "kato_code" => null,
            "is_ip" => false,
            "director_fullname" => null
        );
        foreach($this->languages as $lang){
            $data = $this->parse($bin, $lang);
            
            if($data["okedCode"])  $info["oked_code"] = $data["okedCode"];
            if($data["krpCode"])   $info["krp_code"] = $data["krpCode"];
            if($data["katoCode"])  $info["kato_code"] = $data["katoCode"];
            if($data["fio"])       $info["director_fullname"] = $data["fio"];
            if($data["ip"])         $info["is_ip"] = true;
            $info["full_name_".$lang] = $data["name"];
            $info["full_address_".$lang] = $data["katoAddress"];
        }
        return $info;
    }

    private function parse(string $bin, string $lang) :array{
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->url.'?bin='.$bin.'&lang='.$lang,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if($http_code != 200){
            sleep(2);
            return $this->parse($bin, $lang);
        }
        curl_close($curl);
        $result = json_decode($response, true);
        if(is_array($result)){
            if(isset($result["success"]) && $result["success"])
                return $result["obj"];
        }
        return [];
    }
}

?>