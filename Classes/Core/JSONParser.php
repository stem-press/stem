<?php

namespace Stem\Core;

class JSONParser
{
    public static function parse($jsonString)
    {
        $jsonString = preg_replace("/(^\\s*\\/\\*([^*]|[\r\n]|(\\*+([^*\\/]|[\r\n])))*\\*+\\/)|(^\\s*\\/\\/.*)/m", '', $jsonString);

        return json_decode($jsonString, true);
    }
}
