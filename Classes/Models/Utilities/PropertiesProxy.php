<?php

namespace Stem\Models\Utilities;

use Carbon\Carbon;
use Stem\Core\Context;
use Stem\Models\InvalidPropertiesException;
use Stem\Models\Post;

class PropertiesProxy {
	/** @var array */
	private $props;

	/** @var Post */
	private $post;

	/** @var bool  */
	private $readOnly = false;

	/** @var string[]  */
	private $readOnlyProps = [];

	/** @var int  */
	private $index = 0;

	public function __construct($post, $props, $readOnly = false, $readOnlyProps = [], $index = -1) {
		$this->readOnly = $readOnly;
		$this->post = $post;
		$this->props = $props;
		$this->readOnlyProps = $readOnlyProps;
		$this->index = $index;
	}

	private function getField($name) {
		$field = $this->props[$name];
		if (in_array($field['type'], ['group', 'repeater'])) {
			if ($field['type'] == 'repeater') {
				return new RepeaterPropertiesProxy($this->post, $field, $this->readOnly);
			} else {
				return new PropertiesProxy($this->post, $field['fields'], $this->readOnly);
			}
		} else {
			$acfField = ($this->index >= 0) ? str_replace('{INDEX}',$this->index,$field['field']) : $field['field'];
			$val = $this->post->getField($acfField);

			if (!empty($val)) {
				if (in_array($field['type'], ['image', 'file', 'post_object', 'page'])) {
					$val = ($val instanceof \WP_Post) ? Context::current()->modelForPost($val) : Context::current()->modelForPostID($val);
				} else if ($field['type'] == 'date_picker') {
					$val = Carbon::parse($val);
				}
			}

			return $val;
		}
	}

	public function __get($name) {
		if (!$this->readOnly && !isset($this->readOnlyProps[$name]) && isset($this->props[$name])) {
			return $this->getField($name);
		}
	}

	public function __set($name, $value) {
		if (!$this->readOnly && !isset($this->readOnlyProps[$name]) && isset($this->props[$name])) {
			$field = $this->props[$name];
			if (in_array($field['type'], ['group', 'repeater'])) {
				throw new InvalidPropertiesException("Property {$name} is read-only and cannot be assigned to.");
			} else {
				$this->post->updateField($field['field'], $value);
			}
		}
	}

	public function __call($name, $arguments) {
		if (($this->readOnly || isset($this->readOnlyProps[$name])) && isset($this->props[$name])) {
			return $this->getField($name);
		}
	}
}