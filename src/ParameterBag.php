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

        foreach ($this as $key => $value) {
            if ($callback($key, $value)) {
                return $value;
            }
        }

        return $default;
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
     * @param null|mixed $default
     *
     * @return mixed|string
     */
    public function getAlpha(string $key, $default = null) {
        $value = $this->get($key, false);

        if (empty($value)) {
            return $default;
        }

        return preg_replace('#[^[:alpha:]]#', '', (string) $value);
    }

    /**
     * @param mixed $default
     *
     * @return mixed|string
     */
    public function getAlnum(string $key, $default = null) {
        $value = $this->get($key, false);

        if (empty($value)) {
            return $default;
        }

        return preg_replace('#[^[:alnum:]]#', '', (string) $value);
    }

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function getDigits(string $key, $default = null) {
        /** @var mixed|string $value */
        $value = $this->get($key, false);

        if (empty($value)) {
            return $default;
        }

        return str_replace(['-', '+'], '', (string) $value);
    }

    /**
     * @param mixed $default
     */
    public function getInt(string $key, $default = 0): int {
        return (int) $this->get($key, $default);
    }

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function getBoolean(string $key, $default = false) {
        return $this->filter($key, $default, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function filter(string $key, $default = null, int $filter = FILTER_DEFAULT, array $options = []) {
        $value = $this->get($key, $default);

        // Always turn $options into an array - this allows filter_var option shortcuts.
        if (!\is_array($options) && $options) {
            $options = ['flags' => $options];
        }

        // Add a convenience check for arrays.
        if (\is_array($value) && !isset($options['flags'])) {
            $options['flags'] = FILTER_REQUIRE_ARRAY;
        }

        return filter_var($value, $filter, $options);
    }

    public function isEmpty(): bool {
        return !(bool) $this->count();
    }

    /**
     * @param null|mixed $default
     *
     * @return null|mixed|ParameterBag
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
