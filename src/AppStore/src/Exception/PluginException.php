<?php

declare(strict_types=1);

namespace Mine\AppStore\Exception;

use RuntimeException;

class PluginException extends RuntimeException
{
    public static function notFound(string $name): self
    {
        return new self(sprintf('Plugin [%s] was not found.', $name));
    }

    public static function invalid(string $name, array $errors): self
    {
        return new self(sprintf('Plugin [%s] is invalid: %s', $name, implode('; ', $errors)));
    }
}
