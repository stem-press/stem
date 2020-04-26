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
 * Polyfill for PHP < 7.3
 */
if (!function_exists('array_key_first')) {
	/**
	 * Gets the first key of an array
	 *
	 * @param array $array
	 * @return mixed
	 */
	function array_key_first($array)
	{
		if (!is_array($array) || !count($array)) {
			return null;
		}
		$keys = array_keys($array);
		return $keys[0];
	}
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
 * Determines if an array has all of the specified keys
 * @param array $array
 * @param array $keys
 * @return bool
 */
function arrayHasKeys($array, $keys) {
	foreach($keys as $key) {
		if (!array_key_exists($key, $array)) {
			return false;
		}
	}

	return true;
}

/**
 * Insures all items in the array have a value
 * @param array $set
 *
 * @return bool
 */
function anyEmpty(...$set) {
	foreach($set as $item) {
		if (empty($item)) {
			return true;
		}
	}

	return false;
}


/**
 * Insures all items are not null
 * @param array $set
 *
 * @return bool
 */
function anyNull(...$set) {
	foreach($set as $item) {
		if ($item === null) {
			return true;
		}
	}

	return false;
}


/**
 * Insures all arrays have the same count
 * @param array $set
 *
 * @return bool
 */
function arrayCountsEqual(...$set) {
	$count = null;
	foreach($set as $item) {
		if (($item === null) || !is_array($item)) {
			return false;
		}

		if ($count == null) {
			$count = count($item);
		} else if (count($item) != $count) {
			return false;
		}
	}

	return true;
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

    $files = array_diff(scandir($dir), ['.','..']);
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

function camelCaseString($string) {
	return lcfirst(preg_replace('#[-_]+#', '', ucwords($string, "-_")));
}

function insertKeyedArrayBeforeKey($source, $dest, $keys) {
	$result = [];

	$found = false;
	foreach($dest as $key => $value) {
		if (!$found) {
			if (in_array($key, $keys)) {
				foreach($source as $sourceKey => $sourceValue) {
					$result[$sourceKey] = $sourceValue;
				}

				$result[$key] = $value;
				$found = true;
			} else {
				$result[$key] = $value;
			}
		} else {
			$result[$key] = $value;
		}
	}

	if (!$found) {
		foreach($source as $sourceKey => $sourceValue) {
			$result[$sourceKey] = $sourceValue;
		}
	}

	return $result;
}

function postHasMetaKey($post_id, $meta_key) {
	global $wpdb;
	return $wpdb->get_var($wpdb->prepare("select 1 from {$wpdb->postmeta} where post_id=%d and meta_key=%s", $post_id, $meta_key));
}