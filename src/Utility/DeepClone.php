<?php

namespace Upgate\LaravelJsonRpc\Utility;

use stdClass;

final class DeepClone
{

    public static function deepClone($value)
    {
        return is_object($value)
            ? ($value instanceof stdClass ? self::cloneStdClass($value) : clone $value)
            : (is_array($value) ? self::cloneArray($value) : $value);
    }

    private static function cloneArray(array $array): array
    {
        return array_map(
            function ($value) {
                return self::deepClone($value);
            },
            $array
        );
    }

    private static function cloneStdClass(stdClass $object): stdClass
    {
        $result = new stdClass();
        foreach (get_object_vars($object) as $k => $v) {
            $result->{$k} = self::deepClone($v);
        }

        return $result;
    }

    private function __construct()
    {
    }

}