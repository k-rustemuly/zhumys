<?php

namespace App\Domain\Sign\Company\Service;

use DomainException;
use Predis\ClientInterface;
use App\Helper\Pki;
use App\Domain\Admin\Company\Repository\AdminReadRepository;
use App\Domain\Admin\Company\Repository\AdminUpdaterRepository;
use Firebase\JWT\JWT;
use App\Domain\Company\Repository\CompanyReadRepository;

/**
 * Service.
 */
final class SignIn {

    /**
     * @var AdminReadRepository
     */
    private $readRepository;

    /**
     * @var AdminUpdaterRepository
     */
    private $updateRepository;
    /**
     * @var CompanyReadRepository
     */
    private $companyReadRepository;

    /**
     * @var ClientInterface
     */
    private $redis;

    /**
     * @var Pki
     */
    private $pki;

    /**
     * The constructor.
     *
     * @param AdminReadRepository $readRepository The read repository 
     * @param AdminUpdaterRepository $updateRepository The update repository 
     * @param CompanyReadRepository $companyReadRepository The company read repository 
     * @param ClientInterface   $redis The redis client
     * @param Pki               $pki The pki client
     */
    public function __construct(
        AdminReadRepository $readRepository,
        AdminUpdaterRepository $updateRepository,
        CompanyReadRepository $companyReadRepository,
        ClientInterface $redis, Pki $pki) {
        $this->redis = $redis;
        $this->pki = $pki;
        $this->readRepository = $readRepository;
        $this->updateRepository = $updateRepository;
        $this->companyReadRepository = $companyReadRepository;
    }

    /**
     * Sign in center admin by digital signature.
     *
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     * 
     * @return array<mixed> The result
     */
    public function pkcs(array $post): array{
        $lang = $post["lang"];
        $certInfo = $this->pki->getCertificateInfo($post["base64"], $post["password"], true);
        
        $iin = (string)$certInfo["iin"];

        $adminInfo = $this->readRepository->findByIin($iin);
        if(empty($adminInfo)) {
            throw new DomainException("Company admin not found on database");
        } 
        if(!$adminInfo["is_active"]) {
            throw new DomainException("Company admin is inactive");
        } 

        $bin = $adminInfo["org_bin"];
        $companyInfo = $this->companyReadRepository->getByBin($bin);
        if(!$companyInfo) {
            throw new DomainException("Company not found");
        } 
        if(!$companyInfo["is_active"]) {
            throw new DomainException("Company is inactive");
        }
        $adminInfo["org_name"] = $companyInfo["full_name_".$lang];

        if(!$adminInfo["last_visit"]) {
            $update = array();
            $update["full_name"] = mb_convert_case($certInfo["surname"], MB_CASE_TITLE, "UTF-8")." ".mb_convert_case($certInfo["name"], MB_CASE_TITLE, "UTF-8")." ".mb_convert_case($certInfo["lastname"], MB_CASE_TITLE, "UTF-8");
            if(strlen($certInfo["email"])>2) {
                $update["email"] = strtolower($certInfo["email"]);
            }
            $birthdate = $certInfo["birthdate"];
            list($y,$m,$d) = explode("-", $birthdate);
            if(checkdate((int)$m, (int)$d, (int)$y)) {
                $update["birthdate"] = $certInfo["birthdate"];
            }

            $update["last_visit"] = date("Y-m-d H:i:s");
            $this->updateRepository->updateByBinAndIin($bin, $iin, $update);
        }

        return $this->mapToUserRow($adminInfo);
    }

    /**
     * Map data to row.
     *
     * @param array<mixed> $data The data
     *
     * @return array<mixed> The row
     */
    private function mapToUserRow(array $data): array{
        $payload = $this->mapToUserPayload($data);
        $token = JWT::encode($payload, $_ENV["JWT_KEY"]);
        $refreshToken = $this->generateRefreshToken();
        $refreshData = array("token" => $token,
        "id" => $data["id"],
        "type" => "admin",
        "org_type" => "company",
        "org_bin" => $data["org_bin"]);
        $this->redis->setex($refreshToken, $_ENV["REFRESH_TOKEN_LIVE_SEC"], json_encode($refreshData, JSON_UNESCAPED_UNICODE));

        return [
            "full_name" => $data["full_name"],
            "org_name" => $data["org_name"],
            "token" => $token,
            "refresh_token" => $refreshToken
        ];
    }

    /**
     * Map to user payload to send JWT
     *
     * @param array<mixed> $data The data
     *
     * @return array<mixed> The row
     */
    private function mapToUserPayload(array $data): array{
        return [
            "iss" => $_ENV["API_URL"],
            "aud" => $_ENV["URL"],
            "jti" => $this->generateJti(32),
            "iat" => time(),
            "exp" => time() + intval($_ENV["JWT_LIVE_SEC"]),
            "id" => (int) $data["id"],
            "type" => "admin",
            "org_type" => "company",
            "iin" => $data["iin"],
            "org_bin" => $data["org_bin"]
        ];
    }

    /**
     *  Generating new jtu for user
     *
     * @return string The jti
     */
    private function generateJti($length = 32) {
        if(!isset($length) || intval($length) <= 8 ) {
            $length = 32;
        }
        if (function_exists("random_bytes")) {
            return bin2hex(random_bytes($length));
        }
        if (function_exists("mcrypt_create_iv")) {
            return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
        }
        if (function_exists("openssl_random_pseudo_bytes")) {
            return bin2hex(openssl_random_pseudo_bytes($length));
        }
    }

    /**
     *  Generating new refresh token for user
     *
     * @return string The refresh token
     */
    private function generateRefreshToken(int $length = 36, int $attempt = 1) :string{
        $randomStr = $this->base64url_encode(substr(hash("sha512", mt_rand()), 0, $length));
        if($this->redis->exists($randomStr)) {
            if($attempt > 10) {
                $attempt = 1;
                $length++;
            }
            return $this->generateRefreshToken($length, $attempt);
        }
        return $randomStr;
    }

    /**
     *
     * @param string $data The data
     *
     * @return string The cleaned string
     */
    public function base64url_encode(string $data) :string{ 
        return rtrim(strtr(base64_encode($data), "+/", "-_"), "="); 
    }
}