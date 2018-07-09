<?php

namespace JazzMan\ParameterBag;

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
     * @param mixed $key
     * @param null  $default
     *
     * @return self|mixed|null
     */
    public function get($key, $default = null)
    {
        if ( ! $this->isEmpty() && $this->offsetExists($key) && ! empty($this->offsetGet($key))) {
            $offset = $this->offsetGet($key);
            if ($this->isValidStore($offset)) {
                return new self($offset);
            }

            return $offset;
        }

        return $default;
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
        return (\is_array($store) || \is_object($store)) && ! empty($store);
    }

}