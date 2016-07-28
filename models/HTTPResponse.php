<?php

namespace models;

class HTTPResponse
{
    private static $messagesByCodes = array(
        200 => 'OK',
        201 => 'Created',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable'
    );

    public static function set($code, $message = null) {
        if ($message == '' && isset(self::$messagesByCodes[$code])) {
            $message = self::$messagesByCodes[$code];
        }

        header('HTTP/1.1 ' . $code . ' ' . $message);
    }

    public static function send($code, $message = null) {
        self::set($code, $message);
        die;
    }
}
