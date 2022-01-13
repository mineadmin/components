<?php
declare(strict_types=1);
namespace Xmo\JWTAuth\Util;

use Hyperf\Utils\ApplicationContext;
use Lcobucci\JWT\Configuration;

use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;

use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Parsing\Decoder;
use Lcobucci\JWT\Parsing\Encoder;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\ValidationData;

/**
 * JWT工具类
 */
class JWTUtil
{
    /**
     * claims对象转换成数组
     * @param $claims
     * @return mixed
     */
    public static function claimsToArray(array $claims)
    {
        /**
         *  @var $claim \Lcobucci\JWT\Claim
         */
        foreach($claims as $k => $claim) {
            $claims[$k] = $claim->getValue();
        }
        return $claims;
    }

    /**
     * 处理token
     * @param string $token
     * @param string $prefix
     * @return bool|mixed|string
     */
    public static function handleToken(string $token, string $prefix = 'Bearer')
    {
        if (strlen($token) > 0) {
            $token = ucfirst($token);
            $arr = explode("{$prefix} ", $token);
            $token = $arr[1] ?? '';
            if (strlen($token) > 0) return $token;
        }
        return false;
    }

    public static function getConfiguration(Signer $signer, Key $key)
    {
        return Configuration::forSymmetricSigner($signer, $key);
    }

    /**
     * @see [[Lcobucci\JWT\Builder::__construct()]]
     * @return Builder
     */
    public static function getBuilder(Signer $signer, Key $key)
    {
        return self::getConfiguration($signer, $key)->builder();
    }

    /**
     * @return Parser
     */
    public static function getParser(Signer $signer, Key $key)
    {
        return self::getConfiguration($signer, $key)->parser();
    }

    /**
     * @return ValidationData
     */
    public static function getValidationData(Signer $signer, Key $key, string $token)
    {
        $config = self::getConfiguration($signer, $key);
        $parser = $config->parser()->parse($token);
        $claims = $parser->claims()->all();
        $now = new \DateTimeImmutable();

        if($claims['nbf'] > $now || $claims['exp'] < $now){
            return false;
        }

        $config->setValidationConstraints(new \Lcobucci\JWT\Validation\Constraint\IdentifiedBy($claims['jti']));

        if (! $config->validator()->validate($parser, ...$config->validationConstraints())) {
            return false;
        }

        return true;
    }
}
