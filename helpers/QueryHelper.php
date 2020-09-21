<?php


class QueryHelper
{
    public static function getQueryStringFromFile($path){
        return file_get_contents($path);
    }

    public static function trimQueryString($query) {
        return trim(preg_replace('/\s\s+/', ' ', $query));
    }
}