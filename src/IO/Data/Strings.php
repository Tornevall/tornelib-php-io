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
}