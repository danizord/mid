<?php

namespace MidTest;

use Webimpress\HttpMiddlewareCompatibility\MiddlewareInterface;
use Webimpress\HttpMiddlewareCompatibility\HandlerInterface as RequestHandlerInterface;
use function mid\handler;
use function mid\middleware;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

/**
 * @author Daniel Gimenes
 */
final class TestAsset
{
    public static function counter(): MiddlewareInterface
    {
        return middleware(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            $count   = $request->getAttribute('count', 0);
            $request = $request->withAttribute('count', $count + 1);

            return $handler->handle($request);
        });
    }

    public static function responder(): RequestHandlerInterface
    {
        return handler(function (ServerRequestInterface $request) {
            $response = new Response();

            $response->getBody()->write($request->getAttribute('count'));

            return $response;
        });
    }
}
