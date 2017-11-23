<?php

namespace midTest;

use mid\MiddlewarePipeline;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use function mid\handler;
use function mid\handlerToMiddleware;
use function mid\lazyMiddleware;
use function mid\middleware;
use function mid\middlewareToHandler;
use function mid\pipeline;

/**
 * @author Daniel Gimenes
 */
class MidTest extends TestCase
{
    /**
     * @covers lazyMiddleware()
     */
    public function testLazyMiddleware()
    {
        $handler = TestAsset::responder();
        $called  = false;
        $factory = function () use (&$called) {
            $called = true;

            return TestAsset::counter();
        };

        $middleware = lazyMiddleware($factory);

        $this->assertFalse($called);

        $request  = new ServerRequest();
        $response = $middleware->process($request, $handler);

        $this->assertTrue($called);
        $this->assertSame('1', (string) $response->getBody());
    }

    /**
     * @covers pipeline()
     */
    public function testPipelineWithMiddlewares()
    {
        if (! class_exists(MiddlewarePipeline::class)) {
            $this->markTestSkipped('MiddlewarePipeline is only supported with http-interop/http-middleware ^0.5');
        }

        $handler  = TestAsset::responder();
        $pipeline = pipeline([
            TestAsset::counter(),
            TestAsset::counter(),
            TestAsset::counter(),
        ]);

        $response = $pipeline->process(new ServerRequest(), $handler);

        $this->assertSame('3', (string) $response->getBody());
    }

    /**
     * @covers pipeline()
     */
    public function testEmptyPipeline()
    {
        if (! class_exists(MiddlewarePipeline::class)) {
            $this->markTestSkipped('MiddlewarePipeline is only supported with http-interop/http-middleware ^0.5');
        }

        $pipeline = pipeline();

        $this->assertEquals(new MiddlewarePipeline(), $pipeline);
    }

    /**
     * @covers middlewareToHandler()
     */
    public function testMiddlewareToHandler()
    {
        $middleware = handlerToMiddleware(TestAsset::responder());
        $handler    = middlewareToHandler($middleware);

        $request  = (new ServerRequest())->withAttribute('count', 10);
        $response = $handler->handle($request);

        $this->assertSame('10', (string) $response->getBody());
    }

    /**
     * @covers middlewareToHandler()
     */
    public function testMiddlewareToHandlerThrowsExceptionIfWrappedMiddlewareCallsNextHandler()
    {
        $handler = middlewareToHandler(TestAsset::counter());

        $this->expectException(RuntimeException::class);

        $handler->handle(new ServerRequest());
    }

    /**
     * @covers handlerToMiddleware()
     */
    public function testHandlerToMiddleware()
    {
        $handler    = TestAsset::responder();
        $middleware = handlerToMiddleware(handler(function () {
            return (new Response())->withStatus(404);
        }));

        $request  = new ServerRequest();
        $response = $middleware->process($request, $handler);

        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * @covers middleware()
     */
    public function testMiddleware()
    {
        $middleware = TestAsset::counter();
        $handler    = TestAsset::responder();

        $request  = (new ServerRequest())->withAttribute('count', 1);
        $response = $middleware->process($request, $handler);

        $this->assertSame('2', (string) $response->getBody());
    }

    /**
     * @covers handler()
     */
    public function testHandler()
    {
        $handler = TestAsset::responder();

        $request  = (new ServerRequest())->withAttribute('count', 10);
        $response = $handler->handle($request);

        $this->assertSame('10', (string) $response->getBody());
    }
}
