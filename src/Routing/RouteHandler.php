<?php

namespace CorsDev\SimpleFrameworkCore\Routing;

use CorsDev\SimpleFrameworkCore\Request\Request;
use CorsDev\SimpleFrameworkCore\Response\Response;

interface RouteHandler
{
    public function validate(Request $request): array;

    public function handle(Request $request): ?Response;
}