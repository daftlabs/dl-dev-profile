<?php

namespace Daftswag\Helpers;

class Arr
{
    public static function pluck(array $arr, array $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $arr)) {
                return $arr[$key];
            }
        }
        return null;
    }
}
