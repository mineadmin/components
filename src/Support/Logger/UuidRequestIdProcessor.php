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

namespace Mine\Support\Logger;

use Hyperf\Context\Context;
use Hyperf\Coroutine\Coroutine;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Ramsey\Uuid\Uuid;

final class UuidRequestIdProcessor implements ProcessorInterface
{
    public const REQUEST_ID = 'log.request.id';

    public function __invoke(LogRecord $record)
    {
        $record->extra['request_id'] = self::getUuid();
        return $record;
    }

    public static function getUuid(): string
    {
        $requestId = Context::get(self::REQUEST_ID);
        if ($requestId) {
            return $requestId;
        }
        if (Coroutine::inCoroutine()) {
            $requestId = Context::get(self::REQUEST_ID, null, Coroutine::parentId());
            if ($requestId !== null) {
                self::setUuid($requestId);
                return $requestId;
            }
        }

        return self::setUuid(Uuid::uuid4()->toString());
    }

    public static function setUuid(string $requestId): string
    {
        return Context::set(self::REQUEST_ID, $requestId);
    }
}
