<?php

namespace MidTest;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use function mid\handler;
use function mid\middleware;

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
