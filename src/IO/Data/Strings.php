<?php

namespace TorneLIB\IO\Data;

class Strings
{
    public function __construct()
    {
    }

    public function getCamelCase($string)
    {
        return @array_map("ucfirst", preg_split('/\-|_/', ' '));
    }

    public static function returnCamelCase($string)
    {
        self::getCamelCase($string);
    }
}