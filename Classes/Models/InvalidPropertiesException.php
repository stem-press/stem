<?php

namespace Stem\Models;

use Throwable;

class InvalidPropertiesException extends \Exception {
	private $invalidFields = [];

	public function __construct(string $message = "", array $invalidFields = [], int $code = 0, Throwable $previous = null) {
		$this->invalidFields = $invalidFields;
		parent::__construct($message, $code, $previous);
	}

	public function invalidFields() {
		return $this->invalidFields;
	}
}