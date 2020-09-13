<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-ext/translatable.
 *
 * @link     https://github.com/hyperf-ext/translatable
 * @contact  eric@zhu.email
 * @license  https://github.com/hyperf-ext/translatable/blob/master/LICENSE
 */
namespace HyperfExt\Translatable\Contracts;

use ArrayAccess;
use Hyperf\Utils\Contracts\Arrayable;

interface LocalesInterface extends Arrayable, ArrayAccess
{
    public function add(string $locale): void;

    public function all(): array;

    public function current(): string;

    public function forget(string $locale): void;

    public function get(string $locale): ?string;

    public function getCountryLocale(string $locale, string $country): string;

    public function getLanguageFromCountryBasedLocale(string $locale): string;

    public function getLocaleSeparator(): string;

    public function has(string $locale): bool;

    public function isLocaleCountryBased(string $locale): bool;
}
