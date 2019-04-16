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
				} else if (($field['type'] == 'date_picker') || ($field['type'] == 'date_time_picker')) {
					$val = Carbon::parse($val, Context::timezone());
				}
			}

			return $val;
		}
	}

	public function __get($name) {
		if ($this->__isset($name)) {
			return $this->getField($name);
		}

		return null;
	}

	public function __set($name, $value) {
		if ($this->__isset($name)) {
			if ($this->readOnly && isset($this->readOnlyProps[$name])) {
				throw new InvalidPropertiesException("The property '$name' is read-only.");
			}

			$field = $this->props[$name];
			if (in_array($field['type'], ['group', 'repeater'])) {
				throw new InvalidPropertiesException("Property {$name} is read-only and cannot be assigned to.");
			} else {
				if (in_array($field['type'], ['image', 'file', 'post_object', 'page'])) {
					if ($value instanceof Post) {
						$value = $value->id;
					}
				} else if (($field['type'] == 'date_picker') || ($field['type'] == 'date_time_picker')) {
					if ($value instanceof Carbon) {
						$value->setTimezone(get_option('timezone_string'));
						$value = $value->format("Y-m-d H:i:s");
					}
				}

				$this->post->updateField($field['field'], $value);
			}
		} else {
			throw new InvalidPropertiesException("Unknown property '$name'.");
		}
	}

	public function __isset($name) {
		if (isset($this->readOnlyProps[$name]) || isset($this->props[$name])) {
			return true;
		}

		return false;
	}
}