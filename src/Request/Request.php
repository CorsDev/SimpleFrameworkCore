<?php

namespace CorsDev\SimpleFrameworkCore\Request;

use Exception;
use UnexpectedValueException;
use CorsDev\SimpleFrameworkCore\Routing\Route;

/**
 * @property-read string method
 * @property-read string scheme
 * @property-read string host
 * @property-read string port
 * @property-read string path
 * @property-read string query
 * @property-read array queryParams
 * @property-read array pathParams
 * @property-read mixed body
 */
class Request
{
    private string $method;
    private array $request = [];
    private array $queryParams = [];
    private array $pathParams = [];
    private $body;

    public function __get(string $key)
    {
        return match ($key) {
            'method' => $this->method,
            'pathParams' => $this->pathParams,
            'queryParams' => $this->queryParams,
            'body' => $this->body,
            default => array_key_exists($key, $this->request) ? $this->request[$key] : null,
        };
    }

    public function matches(Route $route): bool
    {

        return $route->matches($this->path) && $route->method == $this->method;
    }

    public static function current(): Request
    {
        return (new self())->setCurrentUrl()->setCurrentMethod()->parseCurrentBody()->parseQueryParams();
    }

    /**
     * @throws Exception
     */
    public function setPathParamsFromRoute(Route $route)
    {
        if (!$route->matches($this->path)) {
            throw new UnexpectedValueException('This request does not match passed route');
        }

        $this->pathParams = $route->pathParams($this->path);
    }

    private function parseCurrentBody(): Request
    {
        if ($this->method == 'get' || $this->method == 'put') {
            return $this;
        }

        $bodyContents = file_get_contents('php://input');

        $decoded = json_decode($bodyContents);

        if ($decoded == null) {
            $this->body = $bodyContents;
        } else {
            $this->body = $decoded;
        }

        return $this;
    }

    private function setCurrentMethod(): Request
    {
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        return $this;
    }

    private function parseQueryParams(): Request
    {
        $params = explode('&', $this->query ?? '');

        foreach ($params as $param) {
            if ($param != "") {
                $paramSegments = explode('=', $param);
                $this->queryParams[$paramSegments[0]] = $paramSegments[1];
            }
        }

        return $this;
    }

    private function setCurrentUrl(): Request
    {
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

        $url = parse_url("$scheme://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

        $this->request = $url;

        return $this;
    }
}