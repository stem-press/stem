<?php

/**
 * Fetches a value from an array using a path string, eg 'some/setting/here'
 * @param $array
 * @param $path
 * @param null $defaultValue
 * @return mixed|null
 */
function arrayPath($array, $path, $defaultValue = null) {
	$pathArray = explode('/', $path);

	$config = $array;

	for ($i = 0; $i < count($pathArray); $i ++) {
		$part = $pathArray[$i];

		if (!isset($config[$part]))
			return $defaultValue;

		if ($i == count($pathArray) - 1) {
			return $config[$part];
		}

		$config = $config[$part];
	}

	return $defaultValue;
}

/**
 * Vomits a dump of data and optionally dies.
 * @param $data
 * @param bool $die
 */
if (!function_exists('vomit')) {
	function vomit($data, $die=true)
	{
		echo '<pre>';
		print_r($data);
		echo '</pre>';

		if ($die)
			die;
	}
}