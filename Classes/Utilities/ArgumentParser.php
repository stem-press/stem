<?php
/**
 * Created by PhpStorm.
 * User: jong
 * Date: 8/18/15
 * Time: 3:48 PM.
 */

namespace Stem\Utilities;

class ArgumentParser
{
    public static function Parse($string)
    {
        $result = [];

        $currentVal = '';
        $currentArray = [];
        $inArray = false;

        $index = 0;
        while ($index < strlen($string)) {
            if ($string[$index] == '[') {
                if ($inArray) {
                    throw new \Exception('Nested arrays not supported');
                }

                if (! empty($currentVal)) {
                    throw new \Exception('Argument string is malformed.');
                }

                $currentArray = [];
                $inArray = true;
            } elseif ($string[$index] == ']') {
                if ($inArray) {
                    if (! empty($currentVal)) {
                        $currentArray[] = trim($currentVal, "\"'\t\n\r\0\x0B");
                    }

                    $result[] = $currentArray;
                }

                $currentVal = '';
                $currentArray = [];
                $inArray = false;
            } elseif (preg_match('#[\/aA-zZ0-9._-]#', $string[$index])) {
                $currentVal .= $string[$index];
            } elseif ($string[$index] == ',') {
                if (! empty($currentVal)) {
                    if ($inArray) {
                        $currentArray[] = trim($currentVal, "\"'\t\n\r\0\x0B");
                    } else {
                        $result[] = trim($currentVal, "\"'\t\n\r\0\x0B");
                    }

                    $currentVal = '';
                }
            } else {
            }

            $index++;
        }

        if (! empty($currentVal)) {
            $result[] = trim($currentVal, "\"'\t\n\r\0\x0B");
        }

        return $result;
    }
}
