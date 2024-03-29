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

namespace Mine\Security\Http\Contract;

use Lcobucci\JWT\UnencryptedToken;

interface BlackContract
{
    /**
     * Add Token to Blacklist.
     */
    public function add(UnencryptedToken $token, array $config = []): bool;

    /**
     * Determine if a token has been blacklisted.
     */
    public function has(array $claims, array $config = []): bool;

    /**
     * Blacklisting removes the token, the key is the jit in the token.
     * @param mixed $key
     */
    public function remove($key, array $config = []): void;

    /**
     * Clear all blacklists.
     */
    public function clear(array $config = []): void;
}
