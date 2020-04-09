<?php

namespace TorneLIB\IO\Data;

class Strings
{
    public function __construct()
    {
    }

    public function getCamelCase($string)
    {
        $return = @lcfirst(@implode(@array_map("ucfirst", preg_split('/\-|_|\s+/', $string))));

        return $return;
    }

    public static function returnCamelCase($string)
    {
        $self = new Strings();
        return $self->getCamelCase($string);
    }

    /**
     * WP Style byte conversion for memory limits.
     *
     * @param $value
     * @return mixed
     */
    public function getBytes($value)
    {
        $value = strtolower(trim($value));
        $bytes = (int)$value;

        if (false !== strpos($value, 't')) {
            $bytes *= 1024 * 1024 * 1024 * 1024;
        } elseif (false !== strpos($value, 'g')) {
            $bytes *= 1024 * 1024 * 1024;
        } elseif (false !== strpos($value, 'm')) {
            $bytes *= 1024 * 1024;
        } elseif (false !== strpos($value, 'k')) {
            $bytes *= 1024;
        } elseif (false !== strpos($value, 'b')) {
            $bytes *= 1;
        }

        // Deal with large (float) values which run into the maximum integer size.
        return min($bytes, PHP_INT_MAX);
    }
}
