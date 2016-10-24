<?php

namespace ILab\Stem\Controllers;

use ILab\Stem\Core\Context;
use ILab\Stem\Core\Controller;
use ILab\Stem\Core\Response;
use Symfony\Component\HttpFoundation\Request;

class ErrorController extends Controller
{
	/**
	 * The current exception.
	 *
	 * @var \Exception
	 */
	static protected $exception = null;
	static protected $statusCode = 200;

	public function __construct(Context $context, $template=null) {
		parent::__construct($context,$template);
	}

	public function getIndex(Request $request) {
		if ($this->template)
			return new Response($this->template, ['exception' => ErrorController::$exception], ErrorController::$statusCode);
	}

	public static function setCurrentError($statusCode, $exception = null) {
		self::$statusCode = $statusCode;
		self::$exception = $exception;
	}

	public static function currentStatusCode() {
		return self::$statusCode;
	}

	public static function currentException() {
		return self::$exception;
	}
}