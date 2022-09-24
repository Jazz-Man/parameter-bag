<?php

use JazzMan\ParameterBag\ParameterBag;

if (!function_exists('app_get_request_data')) {
    function app_get_request_data(): ParameterBag {
        /** @var null|string $method */
        $method = app_get_server_data('REQUEST_METHOD', FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => '/get|post/i',
            ],
        ]);

        /** @var array<string,mixed> $data */
        $data = empty($method) ? (empty($_REQUEST) ? [] : $_REQUEST) : (filter_input_array(
            'POST' === $method ? INPUT_POST : INPUT_GET
        ));

        return new ParameterBag($data);
    }
}

if (!function_exists('app_get_server_data')) {
    /**
     * @param array|int $options
     *
     * @return mixed
     * @phpstan-ignore-next-line
     */
    function app_get_server_data(string $name, int $filter = FILTER_UNSAFE_RAW, $options = FILTER_NULL_ON_FAILURE) {
        if (filter_has_var(INPUT_SERVER, $name)) {
            return filter_input(INPUT_SERVER, $name, $filter, $options);
        }

        return empty($_SERVER[$name]) ?
            null :
            filter_var($_SERVER[$name], $filter, $options);
    }
}
