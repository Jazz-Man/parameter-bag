<?php

namespace JazzMan\ParameterBag;

/**
 * Class ParameterBag.
 */
class ParameterBag extends \ArrayObject
{
    /**
     * Return first result.
     * How to use:
     * <code>
     * <?php
     * $props = new ParameterBag($this->props);.
     *
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
     * @param callable|null $callback
     * @param null          $default
     *
     * @return ParameterBag|mixed|null
     */
    public function first(callable $callback = null, $default = null)
    {
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
     * @param mixed $key
     * @param null  $default
     *
     * @return self|mixed|null
     */
    public function get($key, $default = null)
    {
        if (!$this->isEmpty()) {
            if (strpos($key, '.')) {
                return $this->parseDotNotationKey($key, $default);
            }

            return $this->offsetExists($key) ? $this->offsetGet($key) : $default;
        }

        return $default;
    }

    /**
     * @param string $index
     * @param null   $default
     *
     * @return ParameterBag|mixed|null
     */
    private function parseDotNotationKey($index, $default = null)
    {
        $store = $this->getArrayCopy();
        $keys = explode('.', $index);
        foreach ($keys as $innerKey) {
            if (empty($store[$innerKey])) {
                return $default;
            }

            $store = $store[$innerKey];
        }

        return $store;
    }

    /**
     * Re-index the results array (which by default is non-associative).
     *
     * Drops any item from the results that does not contain the specified key.
     *
     * @param string $key
     *
     * @return $this
     *
     * @throws \RuntimeException
     */
    public function indexBy($key)
    {
        if (!$this->isEmpty()) {
            $tmp_array = [];
            foreach ($this as $values) {
                $tmp_key = null;

                if ($values instanceof self && $values->offsetExists($key)) {
                    $tmp_key = $values->offsetGet($key);
                } elseif (\is_object($values) && !empty($values->$key)) {
                    $tmp_key = $values->$key;
                } elseif (\is_array($values) && !empty($values[$key])) {
                    $tmp_key = $values[$key];
                }

                if (null !== $tmp_key) {
                    $tmp_array[$tmp_key] = $values;
                }
            }
            if (!empty($tmp_array)) {
                $this->exchangeArray($tmp_array);
            } else {
                throw new \RuntimeException("Key ${key} not found");
            }
        }

        return $this;
    }

    /**
     * @param      $key
     * @param null $default
     *
     * @return string|string[]|null
     */
    public function getAlpha($key, $default = null)
    {
        return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default));
    }

    /**
     * @param      $key
     * @param null $default
     *
     * @return string|string[]|null
     */
    public function getAlnum($key, $default = null)
    {
        return preg_replace('/[^[:alnum:]]/', '', $this->get($key, $default));
    }

    /**
     * @param mixed $key
     * @param null  $default
     *
     * @return mixed
     */
    public function getDigits($key, $default = null)
    {
        return str_replace(['-', '+'], '', $this->filter($key, $default, FILTER_SANITIZE_NUMBER_INT));
    }

    /**
     * @param mixed $key
     * @param int   $default
     *
     * @return int
     */
    public function getInt($key, $default = 0)
    {
        return (int) $this->get($key, $default);
    }

    /**
     * @param mixed $key
     * @param bool  $default
     *
     * @return mixed
     */
    public function getBoolean($key, $default = false)
    {
        return $this->filter($key, $default, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param mixed $key
     * @param null  $default
     * @param int   $filter
     * @param array $options
     *
     * @return mixed
     */
    public function filter($key, $default = null, $filter = FILTER_DEFAULT, array $options = [])
    {
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

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return false === (bool) $this->count();
    }
}
