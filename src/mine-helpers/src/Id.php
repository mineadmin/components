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

namespace Mine\Helper;

class Id
{
    public const TWEPOCH = 1620750646000; // 时间起始标记点，作为基准，一般取系统的最近时间（一旦确定不能变动）

    public const WORKER_ID_BITS = 2; // 机器标识位数

    public const DATACENTER_ID_BITS = 2; // 数据中心标识位数

    public const SEQUENCE_BITS = 5; // 毫秒内自增位

    private int $workerId; // 工作机器ID

    private int $datacenterId; // 数据中心ID

    private int $sequence; // 毫秒内序列

    private int $maxWorkerId = -1 ^ (-1 << self::WORKER_ID_BITS); // 机器ID最大值

    private int $maxDatacenterId = -1 ^ (-1 << self::DATACENTER_ID_BITS); // 数据中心ID最大值

    private int $workerIdShift = self::SEQUENCE_BITS; // 机器ID偏左移位数

    private int $datacenterIdShift = self::SEQUENCE_BITS + self::WORKER_ID_BITS; // 数据中心ID左移位数

    private int $timestampLeftShift = self::SEQUENCE_BITS + self::WORKER_ID_BITS + self::DATACENTER_ID_BITS; // 时间毫秒左移位数

    private int $sequenceMask = -1 ^ (-1 << self::SEQUENCE_BITS); // 生成序列的掩码

    private int $lastTimestamp = -1; // 上次生产id时间戳

    public function __construct($workerId = 1, $datacenterId = 1, $sequence = 0)
    {
        if ($workerId > $this->maxWorkerId || $workerId < 0) {
            throw new \Exception("worker Id can't be greater than {$this->maxWorkerId} or less than 0");
        }

        if ($datacenterId > $this->maxDatacenterId || $datacenterId < 0) {
            throw new \Exception("datacenter Id can't be greater than {$this->maxDatacenterId} or less than 0");
        }

        $this->workerId = $workerId;
        $this->datacenterId = $datacenterId;
        $this->sequence = $sequence;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getId(?int $workerId = null)
    {
        $timestamp = $this->timeGen();

        if (! is_null($workerId)) {
            $this->workerId = $workerId;
        }

        if ($timestamp < $this->lastTimestamp) {
            $diffTimestamp = $this->lastTimestamp - $timestamp;
            throw new \Exception("Clock moved backwards.  Refusing to generate id for {$diffTimestamp} milliseconds");
        }

        if ($this->lastTimestamp == $timestamp) {
            $this->sequence = ($this->sequence + 1) & $this->sequenceMask;

            if ($this->sequence == 0) {
                $timestamp = $this->tilNextMillis($this->lastTimestamp);
            }
        } else {
            $this->sequence = 0;
        }

        $this->lastTimestamp = $timestamp;

        return (($timestamp - self::TWEPOCH) << $this->timestampLeftShift) |
            ($this->datacenterId << $this->datacenterIdShift) |
            ($this->workerId << $this->workerIdShift) |
            $this->sequence;
    }

    protected function tilNextMillis($lastTimestamp)
    {
        $timestamp = $this->timeGen();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->timeGen();
        }

        return $timestamp;
    }

    protected function timeGen()
    {
        return floor(microtime(true) * 1000);
    }

    // 左移 <<
    protected function leftShift($a, $b)
    {
        return bcmul($a, bcpow(2, $b));
    }
}
