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

namespace Mine\HttpServer;

use Hyperf\Codec\Json;
use Hyperf\Contract\Jsonable;
use Mine\HttpServer\Constant\HttpResultCode;

final class Result implements Jsonable
{
    public function __construct(
        public bool $success = true,
        public ?string $message = null,
        public null|HttpResultCode|int $code = null,
        public ?array $data = null,
    ) {
        $this->handleCode();
    }

    public function __toString(): string
    {
        return Json::encode($this->toArray());
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getCode(): null|HttpResultCode|int
    {
        return $this->code;
    }

    public function setCode(null|HttpResultCode|int $code): void
    {
        $this->code = $code;
        $this->handleCode();
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        $result = [
            'success' => $this->isSuccess(),
            'requestId' => RequestIdHolder::getId(),
        ];
        if ($this->getMessage()) {
            $result['message'] = $this->getMessage();
        }
        if ($this->getData()) {
            $result['data'] = $this->getData();
        }
        if ($this->getCode()) {
            $result['code'] = $this->getCode();
        }
        return $result;
    }

    public static function success(
        ?string $message = null,
        array|object $data = [],
        HttpResultCode|int $code = 200
    ): self {
        return new self(
            success: true,
            message: $message,
            code: $code,
            data: $data
        );
    }

    public static function error(
        ?string $message = null,
        array|object $data = [],
        HttpResultCode|int $code = 200
    ): self {
        return new self(
            success: false,
            message: $message,
            code: $code,
            data: $data
        );
    }

    private function handleCode(): void
    {
        if ($this->getCode() instanceof HttpResultCode) {
            $trans = $this->getCode()->getTrans();
            $this->setMessage(empty($trans) ? $this->getMessage() : $trans);
            $this->setCode($this->getCode()->value);
        }
    }
}
