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

namespace Mine\NextCoreX\Channel;

use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;
use Mine\NextCoreX\Exception\ModelNotFoundException;
use Mine\NextCoreX\Exception\ModeNotFoundException;

class OrmChannel extends AbstractChannel
{
    protected string $configPrefix = 'orm';

    public function push(string $queue, mixed $data): void
    {
        $this->getQuery()->insert([
            'queue' => $queue,
            'read' => 0,
            'data' => $this->getSerialize()->encode($data),
        ]);
    }

    public function pull(string $queue): mixed
    {
        $raw = $this->first($queue);
        if (empty($raw) || empty($raw['data'])) {
            return null;
        }
        $this->read($raw);
        return $this->getSerialize()->encode($raw['data']);
    }

    public function publish(string $queue, mixed $data): void
    {
        $this->push($queue, $data);
    }

    public function subscribe(string $queue, callable $callback): void
    {
        $interval = $this->getListeningInterval();
        while (true) {
            $raw = $this->first($queue);
            if (empty($raw) || empty($raw['data'])) {
                continue;
            }
            $data = $this->getSerialize()->decode($raw['data']);
            $callback($data, $queue);
            usleep($interval);
        }
    }

    protected function first(string $queue): null|array|object
    {
        return $this->getQuery()
            ->where('read', 0)
            ->where('queue', $queue)
            ->orderBy('created_at')
            ->first();
    }

    protected function read($raw)
    {
        if ($this->getMode() === 'model') {
            $raw->update([
                'read' => 1,
            ]);
        } else {
            $this->getQuery()
                ->where('id', $raw['id'])
                ->update([
                    'read' => 1,
                ]);
        }
    }

    protected function getListeningInterval(): int
    {
        return (int) $this->getConfig('listening.interval', 1000) * 1000;
    }

    protected function getQuery()
    {
        $mode = $this->getMode();
        $table = $this->getTable();
        if ($mode === 'db') {
            return Db::table($table);
        }
        if ($mode === 'model') {
            /**
             * @var class-string<Model> $model
             */
            $model = $this->getModel();
            return $model::query();
        }
        throw new ModeNotFoundException('Couldn\'t find the right mode.' . $mode);
    }

    protected function getMode(): string
    {
        return $this->getConfig('mode', 'db');
    }

    protected function getTable(): string
    {
        return $this->getConfig('table', 'queue');
    }

    protected function getModel(): string
    {
        if ($model = $this->getConfig('model')) {
            return $model;
        }
        throw new ModelNotFoundException('When the configuration item is a mysql driver and the schema is model, the configuration item mysql.model must be a model class name that implements queue,read,created_by');
    }
}
