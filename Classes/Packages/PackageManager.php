<?php

namespace Stem\Packages;

class PackageManager {
	/** @var Package[]  */
	private static $registeredPackages = [];

	/**
	 * Returns all of the registered package instances
	 *
	 * @return Package[]
	 */
	public static function registeredPackages() {
		return static::$registeredPackages;
	}

	/**
	 * Registers a package
	 *
	 * @param Package $package
	 */
	public static function registerPackage($package) {
		static::$registeredPackages[] = $package;
	}
}