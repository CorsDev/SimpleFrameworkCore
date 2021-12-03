<?php

namespace CorsDev\SimpleFrameworkCore\Response;

use JetBrains\PhpStorm\NoReturn;
use JetBrains\PhpStorm\Pure;
use UnexpectedValueException;
use CorsDev\SimpleFrameworkCore\constants\HTTPStatusCodes;

class Response
{
    private int $statusCode = HTTPStatusCodes::OK;
    private array $headers = [];
    private ?string $body = null;

    #[Pure] public static function make(): Response
    {
        return new self();
    }

    public static function makeNotFound(): Response
    {
        return self::make()
            ->statusCode(HTTPStatusCodes::NOT_FOUND)
            ->jsonBody(['message' => HTTPStatusCodes::getMessageForCode(HTTPStatusCodes::NOT_FOUND)]);
    }

    public static function fromException(\Exception $exception): Response
    {
        try {
            return (new self())->statusCode(HTTPStatusCodes::INTERNAL_SERVER_ERROR)
                ->jsonBody(['message' => $exception->getMessage(), 'trace' => $exception->getTrace()]);
        } catch (\Exception $e) {
            return (new self())->statusCode(HTTPStatusCodes::INTERNAL_SERVER_ERROR);
        }
    }

    public function statusCode(int $code): Response
    {
        $this->statusCode = $code;
        return $this;
    }

    public function header(string $key, string $value): Response
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function jsonBody(mixed $body): Response
    {

        $encoded = json_encode($body);

        if ($encoded == null) {
            throw new UnexpectedValueException('The passed body is not a json');
        }

        $this->headers['Content-Type'] = 'application/json';
        $this->body = $encoded;

        return $this;
    }

    public function body(mixed $body): Response
    {
        $this->body = $body;

        return $this;
    }

    #[NoReturn] public function sendAndDie()
    {
        $this->send();
        die;
    }

    public function send()
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $key => $value) {
            header("$key: $value", true);
        }

        echo $this->body;
    }
}