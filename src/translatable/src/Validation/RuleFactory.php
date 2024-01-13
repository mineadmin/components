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

namespace Mine\Translatable\Validation;

use Hyperf\Contract\ConfigInterface;
use Mine\Translatable\Contracts\LocalesInterface;

class RuleFactory
{
    public const FORMAT_ARRAY = 1;

    public const FORMAT_KEY = 2;

    /**
     * @var int
     */
    protected $format;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var string
     */
    protected $suffix;

    /**
     * @var null|array
     */
    protected $locales;

    /**
     * @var LocalesInterface
     */
    protected $helper;

    public function __construct(LocalesInterface $helper, ConfigInterface $config, ?int $format = null, ?string $prefix = null, ?string $suffix = null)
    {
        $this->helper = $helper;
        $this->format = $format ?? $config->get('translatable.rule_factory.format');
        $this->prefix = $prefix ?? $config->get('translatable.rule_factory.prefix');
        $this->suffix = $suffix ?? $config->get('translatable.rule_factory.suffix');
    }

    public static function make(array $rules, ?int $format = null, ?string $prefix = null, ?string $suffix = null, ?array $locales = null): array
    {
        /** @var RuleFactory $factory */
        $factory = \Hyperf\Support\make(static::class, compact('format', 'prefix', 'suffix'));

        $factory->setLocales($locales);

        return $factory->parse($rules);
    }

    public function setLocales(?array $locales = null): self
    {
        if (is_null($locales)) {
            $this->locales = $this->helper->all();

            return $this;
        }

        foreach ($locales as $locale) {
            if (! $this->helper->has($locale)) {
                throw new \InvalidArgumentException(sprintf('The locale [%s] is not defined in available locales.', $locale));
            }
        }

        $this->locales = $locales;

        return $this;
    }

    public function parse(array $input): array
    {
        $rules = [];

        foreach ($input as $key => $value) {
            if (! $this->isTranslatable($key)) {
                $rules[$key] = $value;
                continue;
            }

            foreach ($this->locales as $locale) {
                $rules[$this->formatKey($locale, $key)] = $this->formatRule($locale, $value);
            }
        }

        return $rules;
    }

    protected function formatKey(string $locale, string $key): string
    {
        return $this->replacePlaceholder($locale, $key);
    }

    /**
     * @param mixed|string|string[] $rule
     *
     * @return mixed|string|string[]
     */
    protected function formatRule(string $locale, $rule)
    {
        if (is_string($rule)) {
            if (strpos($rule, '|')) {
                return implode('|', array_map(function (string $rule) use ($locale) {
                    return $this->replacePlaceholder($locale, $rule);
                }, explode('|', $rule)));
            }

            return $this->replacePlaceholder($locale, $rule);
        }
        if (is_array($rule)) {
            return array_map(function ($rule) use ($locale) {
                return $this->formatRule($locale, $rule);
            }, $rule);
        }

        return $rule;
    }

    protected function replacePlaceholder(string $locale, string $value): string
    {
        return preg_replace($this->getPattern(), $this->getReplacement($locale), $value);
    }

    protected function getReplacement(string $locale): string
    {
        switch ($this->format) {
            case self::FORMAT_KEY:
                return '$1:' . $locale;
            default:
            case self::FORMAT_ARRAY:
                return $locale . '.$1';
        }
    }

    protected function getPattern(): string
    {
        $prefix = preg_quote($this->prefix);
        $suffix = preg_quote($this->suffix);

        return '/' . $prefix . '([^\.' . $prefix . $suffix . ']+)' . $suffix . '/';
    }

    protected function isTranslatable(string $key): bool
    {
        return strpos($key, $this->prefix) !== false && strpos($key, $this->suffix) !== false;
    }
}
