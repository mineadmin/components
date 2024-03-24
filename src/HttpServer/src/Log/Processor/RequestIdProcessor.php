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

namespace Mine\HttpServer\Log\Processor;

use Mine\Log\RequestIdHolder;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class RequestIdProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record)
    {
        $record['extra']['request_id'] = RequestIdHolder::getId();
        return $record;
    }
}
