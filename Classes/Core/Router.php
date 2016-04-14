<?php

namespace ILab\Stem\Core;

use Invoker\Invoker;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class Router
 *
 * Handles routing of non-wordpress requests to controllers or callable functions
 *
 * @package ILab\Stem\Core
 */
class Router {
    private $context;
    private $routes;

    public function __construct(Context $context) {
        $this->routes=new RouteCollection();
        $this->context=$context;

        add_action('init',function(){
            if ($this->routes->count()>0) {
                $this->dispatch();
            }
        });
    }

    public function addRoute($name, $routeStr, $destination) {
        if (is_callable($destination))
        {
            $route=new Route($routeStr,['callable'=>$destination]);
            $this->routes->add($name,$route);
        }
        else if (is_string($destination))
        {
            $destination=explode('@',$destination);
            $route=new Route($routeStr,['controller'=>$destination[0],'method'=>$destination[1]]);
            $this->routes->add($name,$route);
        }
    }

    private function dispatch() {
        $req=Request::createFromGlobals();
        $ctx=new RequestContext();
        $ctx->fromRequest($req);

        $matcher=new UrlMatcher($this->routes,$ctx);
        $pi=$req->getPathInfo();

        try {
            $match=$matcher->match($pi);

            $callable=null;
            $controller=null;
            $method=null;

            if (isset($match['callable']))
            {
                $callable = $match['callable'];
            }

            if (isset($match['controller']))
            {
                $controller = $match['controller'];
                $method = $match['method'];
            }

            unset($match['callable']);
            unset($match['_route']);
            unset($match['controller']);
            unset($match['method']);

            $match['request']=$req;

            $response='';
            if ($callable) {
                $invoker=new Invoker();

                $response=$invoker->call($callable,$match);
            }

            if ($controller) {
                if (!class_exists($controller)) {
                    $error = new Response('Invalid method',501);
                    $error->send();
                    die;
                }

                $controllerInst = new $controller($this->context);

                $response = call_user_func_array([$controllerInst, $method],array_values($match));
            }

            if (is_object($response) && ($response instanceof Response))
            {
                $response->send();
            }
            else if (is_string($response))
            {
                echo $response;
            }

            die;
        }
        catch(ResourceNotFoundException $ex) {
            // let wordpress continue doing what it does.
        }
    }
}