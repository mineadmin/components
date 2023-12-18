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

namespace Mine\Translatable\Traits;

use Hyperf\Database\Model\Builder as ModelBuilder;
use Hyperf\Database\Model\Relations\Relation;
use Hyperf\Database\Query\Builder as QueryBuilder;
use Hyperf\Database\Query\JoinClause;

trait Scopes
{
    public function scopeListsTranslations(ModelBuilder $query, string $translationField)
    {
        $withFallback = $this->useFallback();
        $translationTable = $this->getTranslationsTable();
        $localeKey = $this->getLocaleKey();

        $query
            ->select($this->getTable() . '.' . $this->getKeyName(), $translationTable . '.' . $translationField)
            ->leftJoin($translationTable, $translationTable . '.' . $this->getTranslationRelationKey(), '=', $this->getTable() . '.' . $this->getKeyName())
            ->where($translationTable . '.' . $localeKey, $this->locale());

        if ($withFallback) {
            $query->orWhere(function (ModelBuilder $q) use ($translationTable, $localeKey) {
                $q
                    ->where($translationTable . '.' . $localeKey, $this->getFallbackLocale())
                    ->whereNotIn($translationTable . '.' . $this->getTranslationRelationKey(), function (QueryBuilder $q) use (
                        $translationTable,
                        $localeKey
                    ) {
                        $q
                            ->select($translationTable . '.' . $this->getTranslationRelationKey())
                            ->from($translationTable)
                            ->where($translationTable . '.' . $localeKey, $this->locale());
                    });
            });
        }

        return $query;
    }

    public function scopeNotTranslatedIn(ModelBuilder $query, ?string $locale = null)
    {
        $locale = $locale ?: $this->locale();

        return $query->whereDoesntHave('translations', function (ModelBuilder $q) use ($locale) {
            $q->where($this->getLocaleKey(), '=', $locale);
        });
    }

    public function scopeOrderByTranslation(ModelBuilder $query, string $translationField, string $sortMethod = 'asc')
    {
        $translationTable = $this->getTranslationsTable();
        $localeKey = $this->getLocaleKey();
        $table = $this->getTable();
        $keyName = $this->getKeyName();

        return $query
            ->with('translations')
            ->select("{$table}.*")
            ->leftJoin($translationTable, function (JoinClause $join) use ($translationTable, $localeKey, $table, $keyName) {
                $join
                    ->on("{$translationTable}.{$this->getTranslationRelationKey()}", '=', "{$table}.{$keyName}")
                    ->where("{$translationTable}.{$localeKey}", $this->locale());
            })
            ->orderBy("{$translationTable}.{$translationField}", $sortMethod);
    }

    public function scopeOrWhereTranslation(ModelBuilder $query, string $translationField, $value, ?string $locale = null)
    {
        return $this->scopeWhereTranslation($query, $translationField, $value, $locale, 'orWhereHas');
    }

    public function scopeOrWhereTranslationLike(ModelBuilder $query, string $translationField, $value, ?string $locale = null)
    {
        return $this->scopeWhereTranslation($query, $translationField, $value, $locale, 'orWhereHas', 'LIKE');
    }

    public function scopeTranslated(ModelBuilder $query)
    {
        return $query->has('translations');
    }

    public function scopeTranslatedIn(ModelBuilder $query, ?string $locale = null)
    {
        $locale = $locale ?: $this->locale();

        return $query->whereHas('translations', function (ModelBuilder $q) use ($locale) {
            $q->where($this->getLocaleKey(), '=', $locale);
        });
    }

    public function scopeWhereTranslation(ModelBuilder $query, string $translationField, $value, ?string $locale = null, string $method = 'whereHas', string $operator = '=')
    {
        return $query->{$method}('translations', function (ModelBuilder $query) use ($translationField, $value, $locale, $operator) {
            $query->where($this->getTranslationsTable() . '.' . $translationField, $operator, $value);

            if ($locale) {
                $query->where($this->getTranslationsTable() . '.' . $this->getLocaleKey(), $operator, $locale);
            }
        });
    }

    public function scopeWhereTranslationLike(ModelBuilder $query, string $translationField, $value, ?string $locale = null)
    {
        return $this->scopeWhereTranslation($query, $translationField, $value, $locale, 'whereHas', 'LIKE');
    }

    public function scopeWithTranslation(ModelBuilder $query)
    {
        $query->with([
            'translations' => function (Relation $query) {
                if ($this->useFallback()) {
                    $locale = $this->locale();
                    $countryFallbackLocale = $this->getFallbackLocale($locale); // e.g. de-DE => de
                    $locales = array_unique([$locale, $countryFallbackLocale, $this->getFallbackLocale()]);

                    return $query->whereIn($this->getTranslationsTable() . '.' . $this->getLocaleKey(), $locales);
                }

                return $query->where($this->getTranslationsTable() . '.' . $this->getLocaleKey(), $this->locale());
            },
        ]);
    }

    private function getTranslationsTable(): string
    {
        return make($this->getTranslationModelName())->getTable();
    }
}
