<?php

namespace mid;

use Interop\Http\Server\MiddlewareInterface as Middleware;
use Interop\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;

/**
 * Decorates the given middleware and process it only if the incoming request URI has the required prefix.
 *
 * Example:
 *
 *     $pipeline = pipeline();
 *
 *     $pipeline->pipe($cors);
 *     $pipeline->pipe(path('/api', $authentication));
 *
 * While $cors will run for every request, $authentication will run only for requests under `/api` URI prefix.
 *
 */
function path(string $prefix, Middleware $middleware): Middleware
{
    if ('/' === $prefix || '' === $prefix) {
        return $middleware;
    }

    return middleware(function (Request $request, Handler $handler) use ($prefix, $middleware) {
        $prefix = strtolower(rtrim($prefix, '/'));
        $path   = strtolower($request->getUri()->getPath());

        // Skip if current url does not have required prefix
        if (substr($path, 0, strlen($prefix)) !== $prefix) {
            return $handler->handle($request);
        }

        // Otherwise process decorated middleware
        return $middleware->process($request, $handler);
    });
}

/**
 * Takes a callable middleware factory and returns a lazy middleware that calls the factory internally to instantiate
 * the actual middleware only when it is going to be processed.
 *
 * This is specially useful when configuring routed pipelines:
 *
 *     $app->post('/users', lazyMiddleware(function () use ($container) {
 *         $formValidation  = $container->get(FormValidationMiddleware::class);
 *         $formToCommand   = $container->get(FormToCommandMiddleware::class);
 *         $dispatchCommand = $container->get(DispatchCommandMiddleware::class);
 *         $commandResponse = $container->get(CommandResponseMiddleware::class);
 *
 *         return pipeline([$formValidation, $formToCommand, $dispatchCommand, $commandResponse]);
 *     }));
 *
 * @param callable $factory
 *
 * @return Middleware
 */
function lazyMiddleware(callable $factory): Middleware
{
    return middleware(function (Request $request, Handler $handler) use ($factory) {
        $middleware = $factory();

        assert($middleware instanceof Middleware);

        return $middleware->process($request, $handler);
    });
}

/**
 * Creates a middleware pipeline and optionally pipes the middlewares passed as argument.
 *
 * You can pass an array of middlewares as argument:
 *
 *     $pipeline = pipeline([$formValidation, $formToCommand, $dispatchCommand, $commandResponse]);
 *
 * Or you can build the pipeline imperatively:
 *
 *     $pipeline = pipeline();
 *
 *     $pipeline->pipe($formValidation);
 *     $pipeline->pipe($formToCommand);
 *     $pipeline->pipe($dispatchCommand);
 *     $pipeline->pipe($commandResponse);
 *
 * The pipeline can be used as a middleware:
 *
 *     $pipeline->process($request, $handler)
 *
 * Or as a request handler:
 *
 *     $pipeline->handle($request);
 *
 * When used as handler, if the request reach the end of pipeline without a response, it throws a RuntimeException.
 *
 * @param Middleware[] $middlewares
 */
function pipeline(array $middlewares = []): MiddlewarePipeline
{
    $pipeline = new MiddlewarePipeline();

    foreach ($middlewares as $middleware) {
        $pipeline->pipe($middleware);
    }

    return $pipeline;
}

/**
 * Converts a middleware into a request handler.
 * The wrapped middleware must return a response without calling $handler, otherwise it will throw a RuntimeException
 *
 * Example:
 *
 *     middlewareToHandler(new MyMiddleware())->handle($request);
 */
function middlewareToHandler(Middleware $middleware): Handler
{
    return handler(function ($request) use ($middleware) {
        return $middleware->process($request, handler(function () {
            throw new RuntimeException('Middleware was expected to return a response without calling $handler');
        }));
    });
}

/**
 * Converts a request handler into a middleware.
 *
 * Example:
 *
 *     handlerToMiddleware(new MyRequestHandler())->process($request, $handler);
 */
function handlerToMiddleware(Handler $handler): Middleware
{
    return middleware(function (Request $request) use ($handler) {
        return $handler->handle($request);
    });
}

/**
 * Wrapper for callable middlewares.
 *
 * Example:
 *
 *     middleware(function (ServerRequestInterface $request, HandlerInterface $handler): ResponseInterface {
 *         return $handler->handle($request);
 *     });
 */
function middleware(callable $fn): Middleware
{
    return new class($fn) implements Middleware
    {
        private $fn;

        public function __construct(callable $fn)
        {
            $this->fn = $fn;
        }

        public function process(Request $request, Handler $handler): Response
        {
            return ($this->fn)($request, $handler);
        }
    };
}

/**
 * Wrapper for callable request handlers.
 *
 * Example:
 *
 *     handler(function (ServerRequestInterface $request): ResponseInterface {
 *         return new Response(200);
 *     });
 */
function handler(callable $fn): Handler
{
    return new class($fn) implements Handler
    {
        private $fn;

        public function __construct(callable $fn)
        {
            $this->fn = $fn;
        }

        public function handle(Request $request): Response
        {
            return ($this->fn)($request);
        }
    };
}
