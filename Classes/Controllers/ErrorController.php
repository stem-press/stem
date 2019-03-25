<?php

namespace Stem\Controllers;

use Stem\Core\Response;
use Stem\Core\Controller;
use Symfony\Component\HttpFoundation\Request;

class ErrorController extends Controller {
    /**
     * The current exception.
     *
     * @var \Exception
     */
    protected static $exception = null;
    protected static $statusCode = 200;

    public function getIndex(Request $request) {
        if ($this->template) {
            return new Response($this->template, ['exception' => self::$exception], self::$statusCode);
        }

        return null;
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
