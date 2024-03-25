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

namespace Mine\HttpServer\Constant;

use Hyperf\Constants\Annotation\Constants;
use Hyperf\Constants\EnumConstantsTrait;

/**
 * @method string getTrans(array $translate = null)
 */
#[Constants]
enum HttpResultCode: int
{
    use EnumConstantsTrait;

    /**
     * @Trans("success")
     */
    case SUCCESS = 1000;

    /**
     * @Trans("failed")
     */
    case FAILED = 9999;

    /**
     * @Trans("token.expired")
     */
    case TOKEN_EXPIRED = 1001;

    /**
     * @Trans("validate.failed")
     */
    case VALIDATE_FAILED = 1002;

    /**
     * @Trans("no_permission")
     */
    case NO_PERMISSION = 1003;

    /**
     * @Trans("no_data")
     */
    case NO_DATA = 1004;

    /**
     * @Trans("normal_status")
     */
    case NORMAL_STATUS = 1005;

    /**
     * @Trans("no_user")
     */
    case NO_USER = 1010;

    /**
     * @Trans("password_error")
     */
    case PASSWORD_ERROR = 1011;

    /**
     * @Trans("user_ban")
     */
    case USER_BAN = 1012;

    /**
     * @Trans("method_not_allow")
     */
    case METHOD_NOT_ALLOW = 2000;

    /**
     * @Trans("not_found")
     */
    case NOT_FOUND = 2100;

    /**
     * @Trans("interface_exception")
     */
    case INTERFACE_EXCEPTION = 2150;

    /**
     * @Trans("resource_stop")
     */
    case RESOURCE_STOP = 2200;

    /**
     * @Trans("app_ban")
     */
    case APP_BAN = 2300;

    /**
     * @Trans("api_auth_exception")
     */
    case API_AUTH_EXCEPTION = 10000;

    /**
     * @Trans("api_auth_fail")
     */
    case API_AUTH_FAIL = 10010;

    /**
     * @Trans(""api_unbind_app"")
     */
    case API_UNBIND_APP = 10020;

    /**
     * @Trans("api_app_id_missing")
     */
    case API_APP_ID_MISSING = 10101;

    /**
     * @Trans("api_app_secret_missing")
     */
    case API_APP_SECRET_MISSING = 10102;

    /**
     * @Trans("api_access_token_missing")
     */
    case API_ACCESS_TOKEN_MISSING = 10103;

    /**
     * @Trans("api_params_error")
     */
    case API_PARAMS_ERROR = 10104;

    /**
     * @Trans("api_sign_missing")
     */
    case API_SIGN_MISSING = 10105;

    /**
     * @Trans("api_sign_error")
     */
    case API_SIGN_ERROR = 10106;

    /**
     * @Trans("api_identity_missing")
     */
    case API_IDENTITY_MISSING = 10107;

    /**
     * @Trans("api_identity_error")
     */
    case API_IDENTITY_ERROR = 10108;

    /**
     * @Trans("api_verify_pass")
     */
    case API_VERIFY_PASS = 10160;
}
