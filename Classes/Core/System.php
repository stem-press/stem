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
 * Validates the values in an associative array, returning the fields that don't validate
 *
 * @param $array
 * @param $validators
 *
 * @return array
 */
function validateArray($array, $validators) {
	$invalid = [];

	foreach($validators as $key => $filterValidator) {
		if (empty($filterValidator)) {
			continue;
		}

		if (!isset($array[$key]) || !filter_var($array[$key], $filterValidator)) {
			$invalid[] = $key;
		}
	}

	return $invalid;
}

/**
 * Updates a value in an array using a path string, eg 'some/setting/here'.
 * @param $array
 * @param $path
 * @param $value
 */
function updateArrayPath(&$array, $path, $value)
{
    $pathArray = explode('/', $path);

    $config = &$array;

    for ($i = 0; $i < count($pathArray); $i++) {
        $part = $pathArray[$i];

        if ($i == count($pathArray) - 1) {
            $config[$part] = $value;
            return;
        }

        if (!isset($config[$part])) {
            $config[$part] = [];
        }

        $config = &$config[$part];
    }
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

/**
 * Recursively deletes a directory
 *
 * @param $dir
 *
 * @return bool
 */
function nukeDir($dir) {
    if (empty($dir) || !is_dir($dir)) {
        return false;
    }

    if (in_array($dir,['.', '..'])) {
        return false;
    }

    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file") && !is_link($dir)) ? nukeDir("$dir/$file") : unlink("$dir/$file");
    }

    return rmdir($dir);
}


if (! function_exists('vomit')) {
    /*
     * Vomits a dump of data and optionally dies.
     * @param $data
     * @param bool $die
     */
    function vomit($data, $die = true)
    {
        \Stem\Utilities\Debug\VarDumper::dump($data);

        if ($die) {
            die;
        }
    }
}

if (! function_exists('vd')) {
    /*
     * Vomits a dump of data
     * @param $data
     */
    function vd($data)
    {
        \Stem\Utilities\Debug\VarDumper::dump($data);
    }
}

if (!function_exists('keysExist')) {
    function keysExist($array, $keys) {
        foreach($keys as $key) {
            if (!array_key_exists($key, $array)) {
                return false;
            }
        }

        return true;
    }
}