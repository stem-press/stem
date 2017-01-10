<?php

namespace ILab\Stem\Core;

use Symfony\Component\HttpFoundation\Request;

class Response extends \Symfony\Component\HttpFoundation\Response
{
    public static $lastData = [];

    public function __construct($view, $data = [], $status = 200, $headers = [])
    {
        self::$lastData = $data;

        $req = Request::createFromGlobals();
        $accept = $req->headers->get('Accept');
        $is_json = ($accept == 'application/json') || ($accept == 'text/json');

        if ($is_json) {
            $headers['Content-Type'] = $accept;
            $content = json_encode($data);
        } else {
            $content = Context::current()->ui->render($view, $data);
        }

        parent::__construct($content, $status, $headers);
    }

    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();

        if (function_exists('fastcgi_finish_request')) {
            Log::flush();
            //fastcgi_finish_request();
        } elseif ('cli' !== PHP_SAPI) {
            static::closeOutputBuffers(0, true);
        }

        return $this;
    }
}
