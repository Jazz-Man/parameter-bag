<?php

namespace JazzMan\ParameterBag;

use Exception;

/**
 * Class ParameterBag
 *
 * @package JazzMan\ParameterBag
 */
class ParameterBag extends \ArrayObject
{

    /**
     * @return array
     */
    public function all()
    {
        return $this->getArrayCopy();
    }

    /**
     * Return first result.
     * How to use:
     * <code>
     * <?php
     * $props = new ParameterBag($this->props);
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

            return new self(reset($this));
        }
        foreach ($this as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                if ($this->isValidStore($value)) {
                    return new self(reset($this));
                }

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
        if ( ! $this->isEmpty()) {
            if (strpos($key, '.')) {
                return $this->parseDotNotationKey($key, $default);
            }
            if ($this->offsetExists($key) && ! empty($this->offsetGet($key))) {
                $offset = $this->offsetGet($key);
                if ($this->isValidStore($offset)) {
                    return new self($offset);
                }

                return $offset;
            }
        }

        return $default;
    }


    /**
     * @return $this|ParameterBag
     */
    public function toStore()
    {
        if ( ! $this->isEmpty()) {
            $new_store = array_map(function ($item) {

                if ($this->isValidStore($item)) {
                    return new self($item);
                }

            }, $this->getArrayCopy());

            return new self($new_store);

        }

        return $this;
    }

    /**
     * @param string $index
     * @param null   $default
     *
     * @return ParameterBag|mixed|null
     */
    private function parseDotNotationKey($index, $default = null)
    {
        $store = new self($this->getArrayCopy());
        $keys  = explode('.', $index);
        foreach ($keys as $innerKey) {
            if ( ! $store->offsetExists($innerKey)) {
                return $default;
            }
            if ($this->isValidStore($store[$innerKey])) {
                $store = new self($store[$innerKey]);
            } else {
                $store = $store[$innerKey];
            }
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
     * @throws \Exception
     */
    public function indexBy($key)
    {
        if (count($this)) {
            $newResults = [];
            foreach ($this as $values) {
                if (isset($values[$key])) {
                    $newResults[$values[$key]] = $values;
                }
            }
            if ( ! $newResults) {
                throw new Exception("Key ${key} not found");
            }
            $this->exchangeArray($newResults);
        }

        return $this;
    }


    /**
     * @param      $key
     * @param null $default
     *
     * @return null|string|string[]
     */
    public function getAlpha($key, $default = null)
    {
        return preg_replace('/[^[:alpha:]]/', '', $this->get($key, $default));
    }

    /**
     * @param      $key
     * @param null $default
     *
     * @return null|string|string[]
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
        return (int)$this->get($key, $default);
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
        if ( ! is_array($options) && $options) {
            $options = ['flags' => $options];
        }

        // Add a convenience check for arrays.
        if (is_array($value) && ! isset($options['flags'])) {
            $options['flags'] = FILTER_REQUIRE_ARRAY;
        }

        return filter_var($value, $filter, $options);

    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return false === (bool)$this->count();
    }

    /**
     * @param mixed $store
     *
     * @return bool
     */
    private function isValidStore($store)
    {
        return (is_array($store) || is_object($store)) && ! empty($store);
    }

}