<?php

/**
 * Fetches a value from an array using a path string, eg 'some/setting/here'.
 * @param $array
 * @param $path
 * @param null $defaultValue
 * @return mixed|null
 */
function arrayPath($array, $path, $defaultValue = null)
{
    $pathArray = explode('/', $path);

    $config = $array;

    for ($i = 0; $i < count($pathArray); $i++) {
        $part = $pathArray[$i];

        if (! isset($config[$part])) {
            return $defaultValue;
        }

        if ($i == count($pathArray) - 1) {
            return $config[$part];
        }

        $config = $config[$part];
    }

    return $defaultValue;
}

/**
 * Unsets a deep value in multi-dimensional array based on a path string, eg 'some/deep/array/value'.
 * @param $array
 * @param $path
 */
function unsetArrayPath(&$array, $path)
{
    $pathArray = explode('/', $path);

    $config = &$array;

    for ($i = 0; $i < count($pathArray); $i++) {
        $part = $pathArray[$i];

        if (! isset($config[$part])) {
            return;
        }

        if ($i == count($pathArray) - 1) {
            unset($config[$part]);
        }

        $config = &$config[$part];
    }
}

/*
 * Vomits a dump of data and optionally dies.
 * @param $data
 * @param bool $die
 */
if (! function_exists('vomit')) {
    function vomit($data, $die = true)
    {
        \ILab\Stem\Utilities\Debug\VarDumper::dump($data);

        if ($die) {
            die;
        }
    }
}

/*
 * Vomits a dump of data
 * @param $data
 */
if (! function_exists('vd')) {
    function vd($data)
    {
        \ILab\Stem\Utilities\Debug\VarDumper::dump($data);
    }
}
