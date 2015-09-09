<?php

namespace ILab\Stem\Core;

use Symfony\Component\HttpFoundation\Request;

class Response extends \Symfony\Component\HttpFoundation\Response {
    public function __construct($view, $data, $status = 200, $headers = array())
    {
        $req=Request::createFromGlobals();
        $accept=$req->headers->get('Accept');
        $is_json=($accept=='application/json') || ($accept=='text/json');

        if ($is_json)
        {
            $headers['Content-Type']=$accept;
            $content = json_encode($data);
        }
        else
            $content=Context::current()->render($view,$data);

        parent::__construct($content,$status,$headers);
    }
}