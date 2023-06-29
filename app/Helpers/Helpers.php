<?php

namespace App\Helpers;

class Helpers
{
    public static function getSqlWithBindings($query): string
    {
        $bindings = $query->getBindings();

        return preg_replace_callback('/\?/', function ($match) use (&$bindings, $query) {
            return $query->getConnection()->getPdo()->quote(array_shift($bindings));
        }, $query->toSql());
    }
}
