<?php

namespace CorsDev\SimpleFrameworkCore\Routing;

use Exception;
use UnexpectedValueException;

class Route
{
    private const PARAM_REGEX = '([a-zA-Z0-9_]+)';

    private string $pathRegex;
    private array $paramKeys;

    private function __construct(
        public string $path,
        public string $method,
        public RouteHandler $handler
    ) {
        if ($this->path[strlen($this->path) - 1] == '/') {
            $this->path = substr($this->path, 0, -1);
        }

        $this->method = strtolower($this->method);
        $this->generatePathRegex();
    }

    /**
     * @throws Exception
     */
    public static function make(string $path, string $method, string $handlerClass): Route
    {
        $handler = new $handlerClass();

        if (!$handler instanceof RouteHandler) {
            throw new UnexpectedValueException($handler::class.' is not instance of RouteHandler');
        }

        return new Route($path, $method, $handler);
    }

    public function pathParams(string $path): array
    {
        preg_match($this->pathRegex, $path, $matches);
        array_shift($matches);

        $params = [];

        foreach ($matches as $key => $paramValue) {
            $params[$this->paramKeys[$key]] = $paramValue;
        }

        return $params;
    }

    public function matches(string $path): bool
    {
        if ($path[strlen($path) - 1] == '/') {
            $path = substr($path, 0, -1);
        }

        return preg_match($this->pathRegex, $path);
    }

    private function generatePathRegex()
    {
        /** @var array $pathSegments */
        $pathSegments = explode('/', $this->path);

        foreach ($pathSegments as $key => $pathSegment) {

            if (preg_match('/{[a-zA-Z0-9]+}/', $pathSegment) == 1) {
                $this->paramKeys[] = preg_replace('/[{}]/', '', $pathSegment);
                $pathSegments[$key] = self::PARAM_REGEX;
            }

        }

        $this->pathRegex = '/^'.implode('\/', $pathSegments).'$/';
    }
}