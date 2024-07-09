<?php
namespace Plugin\MineAdmin\PayGateway\Model\Enums\PayConfig;

/**
 * 通道
 */
enum Channel: string
{
    /**
     * 支付宝
     */
    case ALIPAY = 'alipay';

    /**
     * 微信
     */
    case WECHAT = 'wechat';

    /**
     * 银联
     */
    case UNI_PAY = 'unipay';

    /**
     * 江苏银行
     */
    case JSB = 'jsb';
}