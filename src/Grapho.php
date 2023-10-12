<?php

namespace AMoschou\Grapho;

use AMoschou\Grapho\App\Classes\DocFolder;

class Grapho
{
    public static function dot($array, $dot = '.', $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, static::dot($value, $dot, $prepend.$key.$dot));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }
}
