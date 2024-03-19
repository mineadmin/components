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

namespace Mine\Translatable\Contracts;

use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\Relations\HasOne;

interface TranslatableInterface
{
    public static function defaultAutoloadTranslations(): void;

    public static function disableAutoloadTranslations(): void;

    public static function enableAutoloadTranslations(): void;

    public static function disableDeleteTranslationsCascade(): void;

    public static function enableDeleteTranslationsCascade(): void;

    public function deleteTranslations($locales = null): void;

    public function getDefaultLocale(): ?string;

    public function getNewTranslation(string $locale): Model;

    public function getTranslation(?string $locale = null, ?bool $withFallback = null): ?Model;

    public function getTranslationOrNew(?string $locale = null): Model;

    public function getTranslationsArray(): array;

    public function hasTranslation(?string $locale = null): bool;

    public function isTranslationAttribute(string $key): bool;

    public function replicateWithTranslations(?array $except = null): Model;

    public function setDefaultLocale(?string $locale);

    public function translate(?string $locale = null, bool $withFallback = false): ?Model;

    public function translateOrDefault(?string $locale = null): ?Model;

    public function translateOrNew(?string $locale = null): Model;

    public function translation(): HasOne;

    public function translations(): HasMany;
}
