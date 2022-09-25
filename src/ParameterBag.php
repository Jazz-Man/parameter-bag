<?php

namespace JazzMan\ParameterBag;

use ArrayObject;

/**
 * Class ParameterBag.
 */
class ParameterBag extends ArrayObject {
    /**
     * Return first result.
     * How to use:
     * <code>
     * <?php
     * $props = new ParameterBag($this->props);.
     *
     * // take first contact without condition
     * $first_contact = $contacts->first();
     *
     * $condition = $props->first(function ($key, $value){
     *  return $value['foo'] === 'bar';
     * });
     *
     * ?>
     * </code>
     *
     * @param mixed $default
     *
     * @return null|mixed|ParameterBag
     */
    public function first(?callable $callback, $default = null) {
        if (null === $callback) {
            if ($this->isEmpty()) {
                return $default;
            }

            return reset($this);
        }

        /**
         * @var string $key
         * @var mixed  $value
         */
        foreach ($this as $key => $value) {
            if ($callback($key, $value)) {
                return $value;
            }
        }

        return $default;
    }

    public function isEmpty(): bool {
        return !(bool) $this->count();
    }

    /**
     * @param mixed $default
     *
     * @return mixed|self
     */
    public function get(string $key, $default = null) {
        if (!$this->isEmpty()) {
            if (strpos($key, '.')) {
                return $this->parseDotNotationKey($key, $default);
            }

            return $this->offsetExists($key) ? $this->offsetGet($key) : $default;
        }

        return $default;
    }

    /**
     * Returns the alphabetic characters of the parameter value.
     */
    public function getAlpha(string $key, string $default = ''): string {
        return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default));
    }

    /**
     * Returns the alphabetic characters and digits of the parameter value.
     */
    public function getAlnum(string $key, string $default = ''): string {
        return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default));
    }

    /**
     * Returns the digits of the parameter value.
     */
    public function getDigits(string $key, string $default = ''): string {
        // we need to remove - and + because they're allowed in the filter
        return str_replace(['-', '+'], '', $this->filter($key, $default, FILTER_SANITIZE_NUMBER_INT));
    }

    /**
     * Returns the parameter value converted to integer.
     */
    public function getInt(string $key, int $default = 0): int {
        return (int) $this->get($key, $default);
    }

    public function getBoolean(string $key, bool $default = false): bool {
        return $this->filter($key, $default, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param mixed                       $default
     * @param array<array-key, mixed>|int $options
     *
     * @return mixed
     */
    public function filter(string $key, $default = null, int $filter = FILTER_DEFAULT, $options = []) {
        $value = $this->get($key, $default);

        /**
         * Always turn $options into an array - this allows filter_var option shortcuts.
         */
        if (!\is_array($options) && $options ) {
            $options = ['flags' => $options];
        }

        /**
         * Add a convenience check for arrays.
         *
         * @var array{flags:mixed,options:mixed} $options
         */
        if (\is_array($value) && empty($options['flags'])) {
            $options['flags'] = FILTER_REQUIRE_ARRAY;
        }

        if ((FILTER_CALLBACK & $filter) && !(($options['options'] ?? null) instanceof \Closure)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'A Closure must be passed to "%s()" when FILTER_CALLBACK is used, "%s" given.',
                    __METHOD__,
                    get_debug_type($options['options'] ?? null)
                )
            );
        }

        return filter_var($value, $filter, $options);
    }

    /**
     * @param mixed $default
     *
     * @return mixed|self
     */
    private function parseDotNotationKey(string $index, $default = null) {
        /** @var array<string,mixed> $store */
        $store = $this->getArrayCopy();
        $keys = explode('.', $index);

        foreach ($keys as $key) {
            if (empty($store[$key])) {
                return $default;
            }

            /** @var array<string,mixed> $store */
            $store = $store[$key];
        }

        return $store;
    }
}
