<?php

namespace Plugin\MineAdmin\PayGateway;

use Plugin\MineAdmin\PayGateway\Service\CrudService;
use Yansongda\Pay\Pay as BigPay;
use Yansongda\Pay\Provider\Alipay;
use Yansongda\Pay\Provider\Jsb;
use Yansongda\Pay\Provider\Unipay;
use Yansongda\Pay\Provider\Wechat;

class Pay
{
    public function __construct(
        private readonly CrudService $service
    ){}

    public function getConfig(): array
    {
        $result = $this->service->toConfigArray();
        $setting = $this->service->getGlobalSetting();
        $result['logger'] = $setting['logger'];
        $result['http'] = $setting['http'];
        return $result;
    }

    public function alipay(): Alipay
    {
        BigPay::config($this->getConfig());
        return BigPay::alipay();
    }

    public function wechat():Wechat
    {
        BigPay::config($this->getConfig());
        return BigPay::wechat();
    }

    public function jsb(): Jsb
    {
        BigPay::config($this->getConfig());
        return BigPay::jsb();
    }

    public function unipay(): Unipay
    {
        BigPay::config($this->getConfig());
        return BigPay::unipay();
    }
}