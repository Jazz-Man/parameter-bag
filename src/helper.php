<?php

use JazzMan\ParameterBag\ParameterBag;

if (!function_exists('app_get_request_data')) {
    function app_get_request_data(): ParameterBag {
        $method = app_get_server_data('REQUEST_METHOD', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => '/get|post/i',
            ],
        ]);

        $data = $method ? filter_input_array(
            'POST' === $method ? INPUT_POST : INPUT_GET
        ) : (!empty($_REQUEST) ? $_REQUEST : []);

        return new ParameterBag((array) $data);
    }
}

if (!function_exists('app_get_server_data')) {
    /**
     * @param array|int $options
     *
     * @return null|mixed
     */
    function app_get_server_data(string $name, int $filter = FILTER_UNSAFE_RAW, $options = FILTER_NULL_ON_FAILURE) {
        if (filter_has_var(INPUT_SERVER, $name)) {
            $data = filter_input(INPUT_SERVER, $name, $filter, $options);
        } else {
            $data = !empty($_SERVER[$name]) ?
                filter_var($_SERVER[$name], $filter, $options) :
                null;
        }

        return $data;
    }
}
