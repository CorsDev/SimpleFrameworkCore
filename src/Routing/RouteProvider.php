<?php

namespace CorsDev\SimpleFrameworkCore\Routing;

use Exception;
use CorsDev\SimpleFrameworkCore\constants\HTTPStatusCodes;
use CorsDev\SimpleFrameworkCore\Request\Request;
use CorsDev\SimpleFrameworkCore\Response\Response;

abstract class RouteProvider
{
    private array $routes = [];

    public function __construct()
    {
        $this->boot();
    }

    abstract public function boot();

    public function addRoute(Route $route): self
    {
        $this->routes[] = $route;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function addRoutes(array $routes): self
    {
        foreach ($routes as $route) {
            if (!$route instanceof Route) {
                throw new Exception($route::class.' is not instance of Route');
            }

            $this->routes[] = $route;
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function handleRequest()
    {

        $request = Request::current();

        /** @var Route $route */
        foreach ($this->routes as $route) {

            if ($request->matches($route)) {

                $request->setPathParamsFromRoute($route);

                $errors = $route->handler->validate($request);

                if (!empty($errors)) {
                    Response::make()->statusCode(HTTPStatusCodes::BAD_REQUEST)->jsonBody([
                        'message' => HTTPStatusCodes::getMessageForCode(HTTPStatusCodes::BAD_REQUEST),
                        'errors' => $errors
                    ])->sendAndDie();
                }

                $response = $route->handler->handle($request);

                $response?->sendAndDie();

                Response::make()->sendAndDie();

            }

        }

        Response::makeNotFound()
            ->sendAndDie();
    }
}