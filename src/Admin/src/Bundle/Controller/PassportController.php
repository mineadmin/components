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

namespace Mine\Admin\Bundle\Controller;

use Lcobucci\JWT\Token;
use Mine\Admin\Bundle\Request\PassportRequest;
use Mine\HttpServer\Result;
use Mine\SecurityBundle\Security;

class PassportController
{
    public function login(
        Security $security,
        PassportRequest $request
    ) {
        /**
         * @var Token $token
         */
        $token = $security->getUserProvider()->retrieveByCredentials($request->validated());
        return Result::success(
            data: [
                'token' => $token->toString(),
            ]
        );
    }
}
