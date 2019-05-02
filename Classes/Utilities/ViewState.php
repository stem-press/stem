<?php

namespace Stem\Utilities;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/**
 * Manages "view state" across multi-page forms.
 * @package Stem\Utilities
 */
class ViewState {
	protected $props = [];
	protected $key = null;
	protected $inputName = '__viewstate';

	/**
	 * ViewState constructor.
	 *
	 * @param string $key
	 * @param string $inputName
	 *
	 * @throws \Defuse\Crypto\Exception\BadFormatException
	 * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
	 * @throws \Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException
	 */
	public function __construct($key, $inputName = '__viewstate') {
		if (!isset($_SESSION)) {
			session_start();
		}

		$this->inputName = $inputName;
		$this->key = Key::loadFromAsciiSafeString($key);

		if (!empty($_SESSION[$this->inputName])) {
			$data = base64_decode($_SESSION[$this->inputName]);
			$viewstate = Crypto::decrypt($data, $this->key);
			$this->props = unserialize($viewstate);
		}
	}

	//region Fetch Form Variables

	/**
	 * Gets a variable from the view state after running through a filter
	 *
	 * @param $name
	 * @param $filter
	 * @param null $defaultValue
	 *
	 * @return mixed|null
	 */
	public function getFilteredVar($name, $filter, $defaultValue = null) {
		return (isset($this->props[$name])) ? filter_var($this->props[$name], $filter) : $defaultValue;
	}

	/**
	 * Gets an integer variable
	 *
	 * @param $name
	 * @param null $defaultValue
	 *
	 * @return int|null
	 */
	public function getInt($name, $defaultValue = null) {
		return $this->getFilteredVar($name, FILTER_SANITIZE_NUMBER_INT, $defaultValue);
	}

	/**
	 * Gets a float variable
	 * @param $name
	 * @param null $defaultValue
	 *
	 * @return float|null
	 */
	public function getFloat($name, $defaultValue = null) {
		return $this->getFilteredVar($name, FILTER_SANITIZE_NUMBER_FLOAT, $defaultValue);
	}

	/**
	 * Gets a string variable
	 * @param $name
	 * @param null $defaultValue
	 *
	 * @return string|null
	 */
	public function getString($name, $defaultValue = null) {
		return $this->getFilteredVar($name, FILTER_SANITIZE_STRING, $defaultValue);
	}

	/**
	 * Gets a filtered email variable
	 * @param $name
	 * @param null $defaultValue
	 *
	 * @return string|null
	 */
	public function getEmail($name, $defaultValue = null) {
		return $this->getFilteredVar($name, FILTER_SANITIZE_EMAIL, $defaultValue);
	}

	/**
	 * Gets a filtered url variable
	 * @param $name
	 * @param null $defaultValue
	 *
	 * @return string|null
	 */
	public function getUrl($name, $defaultValue = null) {
		return $this->getFilteredVar($name, FILTER_SANITIZE_URL, $defaultValue);
	}

	/**
	 * Gets a filtered boolean variable
	 * @param $name
	 * @param null $defaultValue
	 *
	 * @return string|null
	 */
	public function getBool($name, $defaultValue = null) {
		return ($this->getString($name) == 'on') ?: $defaultValue;
	}

	/**
	 * Returns the count for an indexed array variable
	 * @param $name
	 * @param null $defaultValue
	 *
	 * @return string|null
	 */
	public function getCount($name) {
		if (isset($this->props[$name]) && is_array($this->props[$name])) {
			return count($this->props[$name]);
		}

		return 0;
	}

	/**
	 * Gets a raw indexed variable
	 * @param $name
	 * @param $index
	 *
	 * @return mixed|null
	 */
	protected function getRawIndexedVar($name, $index) {
		if (!isset($this->props[$name]) || !is_array($this->props[$name])) {
			return null;
		}

		if ($index >= count($this->props[$name])) {
			return null;
		}

		return $this->props[$name][$index];
	}

	/**
	 * Gets an indexed variable after running through a filter
	 *
	 * @param $name
	 * @param $index
	 * @param $filter
	 * @param null $defaultValue
	 *
	 * @return mixed|null
	 */
	public function filteredIndexedVar($name, $index, $filter, $defaultValue = null) {
		return filter_var($this->getRawIndexedVar($name, $index), $filter) ?: $defaultValue;
	}

	/**
	 * Gets an indexed int variable
	 * @param $name
	 * @param $index
	 * @param null $defaultValue
	 *
	 * @return int|null
	 */
	public function getIndexedInt($name, $index, $defaultValue = null) {
		return $this->filteredIndexedVar($name, $index, FILTER_SANITIZE_NUMBER_INT, $defaultValue);
	}

	/**
	 * Gets an indexed float variable
	 * @param $name
	 * @param $index
	 * @param null $defaultValue
	 *
	 * @return float|null
	 */
	public function getIndexedFloat($name, $index, $defaultValue = null) {
		return $this->filteredIndexedVar($name, $index, FILTER_SANITIZE_NUMBER_FLOAT, $defaultValue);
	}

	/**
	 * Gets an indexed string variable
	 * @param $name
	 * @param $index
	 * @param null $defaultValue
	 *
	 * @return string|null
	 */
	public function getIndexedString($name, $index, $defaultValue = null) {
		return $this->filteredIndexedVar($name, $index, FILTER_SANITIZE_STRING, $defaultValue);
	}

	/**
	 * Gets an indexed email variable
	 * @param $name
	 * @param $index
	 * @param null $defaultValue
	 *
	 * @return string|null
	 */
	public function getIndexedEmail($name, $index, $defaultValue = null) {
		return $this->filteredIndexedVar($name, $index, FILTER_SANITIZE_EMAIL, $defaultValue);
	}

	/**
	 * Gets an indexed url variable
	 * @param $name
	 * @param $index
	 * @param null $defaultValue
	 *
	 * @return string|null
	 */
	public function getIndexedUrl($name, $index, $defaultValue = null) {
		return $this->filteredIndexedVar($name, $index, FILTER_SANITIZE_URL, $defaultValue);
	}

	/**
	 * Gets an indexed boolean variable
	 * @param $name
	 * @param $index
	 * @param null $defaultValue
	 *
	 * @return bool|null
	 */
	public function getIndexedBool($name, $index, $defaultValue = null) {
		return ($this->getIndexedString($name, $index) == 'on') ?: $defaultValue;
	}

	/**
	 * Returns all props in the view state
	 * @return array
	 */
	public function getProps() {
		return $this->props;
	}
	//endregion

	//region Set Variables

	public function setInt($name, $value) {
		return $this->props[$name] = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
	}

	public function setFloat($name, $value) {
		return $this->props[$name] = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT);
	}

	public function setString($name, $value) {
		return $this->props[$name] = filter_var($value, FILTER_SANITIZE_STRING);
	}

	public function setEmail($name, $value) {
		return $this->props[$name] = filter_var($value, FILTER_SANITIZE_EMAIL);
	}

	public function setUrl($name, $value) {
		return $this->props[$name] = filter_var($value, FILTER_SANITIZE_URL);
	}

	public function setBool($name, $value) {
		return $this->props[$name] = (empty($value)) ? null : 'on';
	}

	//endregion

	//region Loading/Saving

	/**
	 * Merges the current post variables into the view state
	 */
	public function mergePost() {
		if (!isset($_POST)) {
			return;
		}

		$post = $_POST;
		if (is_array($post)) {
			if (isset($post[$this->inputName])) {
				$data = base64_decode($post[$this->inputName]);
				$viewstate = Crypto::decrypt($data, $this->key);
				$props = unserialize($viewstate);
				$this->props = array_merge($this->props, $props);

				unset($post[$this->inputName]);
			}

			$this->props = array_merge($this->props, $post);
		}
	}

	/**
	 * Saves the view state to the session
	 * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
	 */
	public function save() {
		$data = serialize($this->props);
		$encryptedData = Crypto::encrypt($data, $this->key);
		$_SESSION[$this->inputName] = base64_encode($encryptedData);
	}

	public function delete() {
		unset($_SESSION[$this->inputName]);
	}

	public function render() {
		$data = serialize($this->props);
		$encryptedData = Crypto::encrypt($data, $this->key);
		$viewstate = base64_encode($encryptedData);

		return "<input type='hidden' name='{$this->inputName}' value='{$viewstate}'>";
	}

	//endregion
}