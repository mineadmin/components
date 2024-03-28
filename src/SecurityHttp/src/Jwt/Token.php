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

namespace Mine\Security\Http\Jwt;

use Hyperf\Context\ApplicationContext;
use Mine\Security\Http\Constant\TokenValidConstant;
use Mine\Security\Http\Exception\TokenValidException;
use Mine\Security\Http\Support\Jwt;
use Mine\SecurityBundle\Config;
use Mine\SecurityBundle\Contract\TokenInterface;
use Mine\SecurityBundle\Contract\UserInterface;
use Psr\Http\Message\RequestInterface;

class Token implements TokenInterface
{
    public function __construct(
        private readonly Jwt $jwt,
        private readonly Config $config
    ) {}

    public function user(...$param): ?UserInterface
    {
        $request = $this->getRequest();
        if ($request === null) {
            throw new \RuntimeException('Request is not available.');
        }
        if ($request->hasHeader('Authorization')) {
            throw new TokenValidException('Token is not available.', TokenValidConstant::TOKEN_NOT_FOUND);
        }
        $token = str_replace('Bearer ', '', $request->getHeaderLine('Authorization'));
        $scene = $param[0] ?? 'default';
        $resolveToken = $this->jwt->parse($token, $scene);
        $attributes = $resolveToken->claims()->all();
        $entity = $this->getUserEntity();
        foreach ($attributes as $key => $value) {
            $entity->setAttribute(str_replace('__attribute__', '', $key), $value);
        }
        return $entity;
    }

    private function getUserEntity(): UserInterface
    {
        return new ($this->config->get('entity'));
    }

    private function getRequest(): ?RequestInterface
    {
        return ApplicationContext::getContainer()->get(RequestInterface::class);
    }
}
