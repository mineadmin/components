<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace Xmo\JWTAuth\Util;

use Lcobucci\JWT\Claim;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\ValidationData;
use Xmo\JWTAuth\Exception\TokenExpireException;

/**
 * JWT工具类.
 */
class JWTUtil
{
    /**
     * claims对象转换成数组.
     * @return mixed
     */
    public static function claimsToArray(array $claims)
    {
        /**
         * @var Claim $claim
         */
        foreach ($claims as $k => $claim) {
            $claims[$k] = $claim->getValue();
        }
        return $claims;
    }

    /**
     * 处理token.
     * @return bool|mixed|string
     */
    public static function handleToken(string $token, string $prefix = 'Bearer')
    {
        if (strlen($token) > 0) {
            $token = ucfirst($token);
            $arr = explode("{$prefix} ", $token);
            $token = $arr[1] ?? '';
            if (strlen($token) > 0) {
                return $token;
            }
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
    public static function getValidationData(Signer $signer, Key $key, string $token): bool
    {
        $config = self::getConfiguration($signer, $key);
        $parser = $config->parser()->parse($token);
        $claims = $parser->claims()->all();
        $now = new \DateTimeImmutable();

        // 这是基于用户给定的。不是后端解析后来判断的
        if ($claims['nbf'] > $now || $claims['exp'] < $now) {
            throw new TokenExpireException('Token has expired');
        }

        $config->setValidationConstraints(new IdentifiedBy($claims['jti']));
        $config->setValidationConstraints(new SignedWith($signer, $key));

        if (! $config->validator()->validate($parser, ...$config->validationConstraints())) {
            return false;
        }

        return true;
    }
}
