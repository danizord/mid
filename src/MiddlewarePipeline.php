<?php

namespace mid;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use const Webimpress\HttpMiddlewareCompatibility\HANDLER_METHOD;

/**
 * In http-interop/http-middleware ^0.4, both middleware and handler interfaces define a method process,
 * so this implementation can' support it since it implements both interfaces. Therefore, it requires
 * http-interop/http-middleware ^0.5
 */
if ('handle' === HANDLER_METHOD) {
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
}
