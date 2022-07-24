<?php

declare(strict_types=1);

namespace CEmerson\Auth\AuthContexts\Middleware;

use CEmerson\Auth\AuthContexts\Psr7AuthContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Psr15AuthMiddleware implements MiddlewareInterface
{
    private Psr7AuthContext $psr7AuthContext;

    public function __construct(Psr7AuthContext $psr7AuthContext)
    {
        $this->psr7AuthContext = $psr7AuthContext;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->psr7AuthContext->populateContextFromPsr7Request($request);

        $response = $handler->handle($request);

        return $this->psr7AuthContext->addContextValuesToResponse($response);
    }
}
