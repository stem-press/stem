<?php

namespace Stem\Core;

use Invoker\Invoker;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Class Router.
 *
 * Handles routing of non-wordpress requests to controllers or callable functions
 */
class Router
{
    private $context;
    private $routes;
    private $earlyRoutes;

    public function __construct(Context $context) {
        $this->routes = new RouteCollection();
        $this->earlyRoutes = new RouteCollection();
        $this->context = $context;
    }

    public function addRoute($early, $name, $routeStr, $destination, $defaults = [], $requirements = [], $methods = []) {
        if (is_callable($destination)) {
            $defaults['callable'] = $destination;
            $route = new Route($routeStr, $defaults, $requirements, [], '', [], $methods);

            if ($early) {
                $this->earlyRoutes->add($name, $route);
            } else {
                $this->routes->add($name, $route);
            }
        } elseif (is_string($destination)) {
            $destination = explode('@', $destination);
            $defaults['controller'] = $destination[0];
            $defaults['method'] = $destination[1];
            $route = new Route($routeStr, $defaults, $requirements, [], '', [], $methods);

            if ($early) {
                $this->earlyRoutes->add($name, $route);
            } else {
                $this->routes->add($name, $route);
            }
        }
    }

    /**
     * Dispatches the request.  Returns true if dispatched, false if no routes match
     *
     * @param bool $early For matching routes that should happen before WordPress loads completely
     * @param Request $req
     * @return bool
     * @throws \Invoker\Exception\InvocationException
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     */
    public function dispatch($early, Request $req) {
        $routeCount = ($early) ? $this->earlyRoutes->count() : $this->routes->count();
        if ($routeCount == 0) {
            return false;
        }

        $ctx = new RequestContext();
        $ctx->fromRequest($req);

        $matcher = new UrlMatcher(($early) ? $this->earlyRoutes : $this->routes, $ctx);
        $pi = $req->getPathInfo();

        try {
            $match = $matcher->match($pi);

            $callable = null;
            $controller = null;
            $method = null;

            if (isset($match['callable'])) {
                $callable = $match['callable'];
            }

            if (isset($match['controller'])) {
                $controller = $match['controller'];
                $method = $match['method'];
            }

            unset($match['callable']);
            unset($match['_route']);
            unset($match['controller']);
            unset($match['method']);

            $match['request'] = $req;

            $response = '';
            if ($callable) {
                $invoker = new Invoker();

                $response = $invoker->call($callable, $match);
            }

            if ($controller) {
                if (! class_exists($controller)) {
                    $error = new Response('Invalid method', 501);
                    $error->send();
                    die;
                }

                $controllerInst = new $controller($this->context);
                $response = call_user_func_array([$controllerInst, $method], array_values($match));
            }

            if (is_object($response) && ($response instanceof Response)) {
                $response->send();
            } elseif (is_string($response)) {
                echo $response;
            }

            die;
        } catch (MethodNotAllowedException $mex) {
            // let wordpress continue doing what it does.
            $response = new Response('Method not allowed', 405);
            $response->send();
            return true;
        } catch (ResourceNotFoundException $ex) {
            // let wordpress continue doing what it does.
            return false;
        }
    }
}
