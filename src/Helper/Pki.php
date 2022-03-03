<?php
declare(strict_types=1);

namespace App\Helper;
use Malikzh\PhpNCANode\ApiErrorException;
use Malikzh\PhpNCANode\CurlException;
use Malikzh\PhpNCANode\InvalidResponseException;
use Malikzh\PhpNCANode\NCANodeException;
use Malikzh\PhpNCANode\NCANodeClient;
use DomainException;

class Pki{

    /**
     *
     * @var NCANodeClient $nca НСА для работы с ЭЦП 
     */
    private $nca; 

    /**
     *
     * @var bool $bVerifyOcsp Провести проверку на отозванность через OCSP. 
     */
    private $bVerifyOcsp; 

    /**
     *
     * @var bool $bVerifyCrl Провести проверку на отозванность через CRL.
     */
    private $bVerifyCrl; 

    /**
     *
     * @var array $cert Информация о сертификате.
     */
    public $cert = [];

    public function __construct(string $domain, bool $isVerifyOcsp = false, bool $isVerifyCrl = false)
    {
        $this->nca = new NCANodeClient($domain);
        $this->bVerifyOcsp = $isVerifyOcsp;
        $this->bVerifyCrl = $isVerifyCrl;
    }

    /**
     * Возвращает информацию о P12-файле
     * 
     * @param string        $p12Base64  Закодированный в Base64 файл P12
     * @param string        $sPassword  Пароль к файлу
     * @param bool          $is_auth    Тип эцп Auth?
     * 
     * @throws DomainException
     * 
     * @return mixed $result
     *
     */
    public function getCertificateInfo(string $p12Base64, string $sPassword, bool $is_auth = true):array
    {
        try{
            $info = $this->nca->pkcs12Info($p12Base64, $sPassword, $this->bVerifyOcsp, $this->bVerifyCrl);
            if($info->isExpired()) throw new DomainException("The certificate has expired");
            if($_ENV["API_IS_DEBUG"] == "false" && !$info->isLegal()) throw new DomainException("The certificate is not legal");
            if($is_auth && $info->keyUsage != "AUTH") throw new DomainException("The key is not intended for authentication");
            if(!$is_auth && $info->keyUsage != "SIGN") throw new DomainException("The key is not intended for sign");
            if(!isset($info->subject)) throw new DomainException("No certificate holder information found");
            if(!isset($info->keyUser)) throw new DomainException("The parameter by which you can determine the holder of the certificate (key) is a legal entity. face or not, it is not");
            
            $subject_info = $info->subject;
            $this->addToCert($subject_info, "lastName", "lastname")
                ->addToCert($subject_info, "country", "country_code")
                ->addToCert($subject_info, "gender", "gender", "UNDEFINED")
                ->addToCert($subject_info, "surname", "surname")
                ->addToCert($subject_info, "locality", "locality", "UNDEFINED")
                ->addToCert($subject_info, "state", "state", "UNDEFINED")
                ->addToCert($subject_info, "birthDate", "birthdate", "1900-01-01")
                ->addToCert($subject_info, "email")
                ->addToCert($subject_info, "iin")
                ->addToCert($subject_info, "bin")
                ->addToCert($subject_info, "organization");
            $commonName = array_key_exists('commonName', $subject_info) ? $subject_info["commonName"] : null;
            list($surname,$name) = explode(' ',$commonName,2);

            $this->cert["surname"] = mb_convert_case($this->cert["surname"], MB_CASE_TITLE, 'UTF-8');
            $this->cert["name"] = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
            $this->cert["lastname"] = mb_convert_case($this->cert["lastname"], MB_CASE_TITLE, 'UTF-8');
            $this->cert["full_name"] = $this->cert["surname"]." ".$this->cert["name"]." ".$this->cert["lastname"];
            $keyUser = $info->keyUser;
            $this->cert["is_individual"] = in_array("INDIVIDUAL", $keyUser);
            $this->cert["is_legal_entity"] = in_array("ORGANIZATION", $keyUser);
            $this->cert["is_ceo"] = in_array("CEO", $keyUser);
            $this->cert["is_can_sign"] = in_array("CAN_SIGN", $keyUser);
            $this->cert["is_can_sign_financial"] = in_array("CAN_SIGN_FINANCIAL", $keyUser);
            $this->cert["is_hr"] = in_array("HR", $keyUser);
            $this->cert["is_employee"] = in_array("EMPLOYEE",$keyUser);
        }catch(ApiErrorException $e){
            throw new DomainException("Wrong password or corrupted file");
        }catch(CurlException $e){
            throw new DomainException("Server pki error");
        }catch(InvalidResponseException $e){
            throw new DomainException("Server pki error");
        }catch(NCANodeException $e){
            throw new DomainException("Server pki error");
        } 
        return $this->cert;
    }

    /**
     * Добавляет данные о сертификате
     * 
     * @param array         $arr        Массив данных
     * @param string        $key        Ключ для поиска и добавление данных
     * @param mixed         $default    Данный по умолчиванию
     * 
     * @return self
     *
     */
    private function addToCert(array $arr, string $key, string $newKey = null, $default = null){
        $newKey = $newKey?:$key;
        $this->cert[$newKey] = array_key_exists($key, $arr) ? $arr[$key] : $default;
        return $this;
    }
    

    /**
     * Подписывает XML
     *
     * @param string $xml XML данные, которые надо подписать
     * @param string $p12Base64 Закодированный в Base64, файл P12
     * @param string$sPassword Пароль к файлу p12
     * @return mixed Результат подписания
     * @throws DomainException Произошла ошибка со стороны API. Неверный сертификат, неверный пароль и т.д.
     */
    public function sign($sXml, $p12Base64, $sPassword)
    {
        try{
            return $this->nca->xmlSign($sXml, $p12Base64, $sPassword);
        }catch(ApiErrorException $e){
            throw new DomainException("Wrong password or corrupted file");
        }catch(CurlException $e){
            throw new DomainException("Server pki error");
        }catch(InvalidResponseException $e){
            throw new DomainException("Server pki error");
        }catch(NCANodeException $e){
            throw new DomainException("Server pki error");
        }
    }
}

?>