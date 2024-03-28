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

namespace Mine\Security\Http;

class TokenObject
{
    public string $issuedBy = '';

    public array $claims = [];

    public array $headers = [];

    public bool $isInsertSsoBlack = true;

    public function getIssuedBy(): string
    {
        return $this->issuedBy;
    }

    public function setIssuedBy(string $issuedBy): void
    {
        $this->issuedBy = $issuedBy;
    }

    public function getClaims(): array
    {
        return $this->claims;
    }

    public function setClaims(array $claims): void
    {
        $this->claims = $claims;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }
}
