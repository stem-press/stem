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

/**
 * Diffs two arrays and returns an array with elements that have changed or been added.  Handles
 * nested arrays and objects that can be serialized.
 *
 * @param $newArray
 * @param $originalArray
 *
 * @return array
 */
function diffArray($newArray, $originalArray) {
	$result = [];

	$keys = array_keys($newArray);
	foreach($keys as $key) {
		if (array_key_exists($key, $originalArray)) {
			$lval = $newArray[$key];
			$rval = $originalArray[$key];
			if (!is_array($lval) && !is_array($rval) && !is_object($lval) && !is_object($rval)) {
				if (((string)$lval) != ((string)$rval)) {
					$result[$key] = $lval;
				}
			} else if (is_array($lval) && is_array($rval)) {
				if (count($lval) != count($rval)) {
					$result[$key] = $lval;
				} else  if (count(diffArray($lval, $rval))>0) {
					$result[$key] = $lval;
				}
			} else if (is_object($lval) && is_object($rval)) {
				if (serialize($lval) != serialize($rval)) {
					$result[$key] = $lval;
				}
			}
		} else {
			$result[$key] = $newArray[$key];
		}
	}

	return $result;
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