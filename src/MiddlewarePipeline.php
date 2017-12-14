<?php

namespace mid;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * @author Daniel Gimenes
 */
final class MiddlewarePipeline implements RequestHandlerInterface, MiddlewareInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    private $middlewares;

    public function pipe(MiddlewareInterface $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $nextHandler = clone $this;

        $nextHandler->middlewares[] = handlerToMiddleware($handler);

        return $nextHandler->handle($request);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (empty($this->middlewares)) {
            throw new RuntimeException('The request reached the end of middleware pipeline without a response');
        }

        $nextHandler = clone $this;
        $middleware  = array_shift($nextHandler->middlewares);

        return $middleware->process($request, $nextHandler);
    }
}
