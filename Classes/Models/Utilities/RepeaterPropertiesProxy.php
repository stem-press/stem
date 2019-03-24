<?php

namespace Stem\Models\Utilities;

use Stem\Core\Context;
use Stem\Models\InvalidPropertiesException;
use Stem\Models\Post;

class RepeaterPropertiesProxy implements \ArrayAccess, \Countable, \Iterator {
	/** @var array */
	private $field;

	/** @var Post */
	private $post;

	/** @var bool  */
	private $readOnly = false;

	/** @var PropertiesProxy[]  */
	private $childProxies = [];

	public function __construct($post, $field, $readOnly = false) {
		$this->readOnly = $readOnly;
		$this->post = $post;
		$this->field = $field;
		$count = $this->post->getField($field['field']);
		for($i = 0; $i < $count; $i++) {
			$this->childProxies[] = new PropertiesProxy($post, $field['fields'], $readOnly, [], $i);
		}
	}

	//region Iterator
	public function current() {
		return current($this->childProxies);
	}

	public function next() {
		next($this->childProxies);
	}

	public function key() {
		return key($this->childProxies);
	}

	public function valid() {
		$key = key($this->childProxies);
		return (($key !== null) && ($key !== false));
	}

	public function rewind() {
		reset($this->childProxies);
	}
	//endregion

	//region ArrayAccess
	public function offsetExists($offset) {
		return isset($this->childProxies[$offset]);
	}


	public function offsetGet($offset) {
		return (isset($this->childProxies[$offset])) ? $this->childProxies[$offset] : null;
	}

	public function offsetSet($offset, $value) {
		throw new \Exception("This array is read-only");
	}

	public function offsetUnset($offset) {
		throw new \Exception("This array is read-only");
	}
	//endregion

	//region Countable
	public function count(){
		return count($this->childProxies);
	}
	//endregion
}