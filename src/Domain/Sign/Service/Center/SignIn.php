<?php

namespace App\Domain\Sign\Service\Center;

use DomainException;
use Predis\ClientInterface;
use App\Helper\Pki;

/**
 * Service.
 */
final class SignIn{
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
     * @param ClientInterface   $redis The redis client
     * @param Pki               $pki The pki client
     */
    public function __construct(ClientInterface $redis, Pki $pki){
        $this->redis = $redis;
        $this->pki = $pki;
    }

    /**
     * Sign in user by email address.
     *
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     * 
     * @return array<mixed> The result
     */
    public function pkcs(array $post): array{
        return $this->pki->getCertificateInfo($post["base64"], $post["password"], true);
    }
}