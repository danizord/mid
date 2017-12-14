<?php

namespace midTest;

use mid\MiddlewarePipeline;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Zend\Diactoros\ServerRequest;
use function mid\handlerToMiddleware;

/**
 * @author Daniel Gimenes
 */
final class MiddlewarePipelineTest extends TestCase
{
    public function testCanBeUsedAndReusedAsMiddleware()
    {
        $pipeline = new MiddlewarePipeline();
        $counter  = TestAsset::counter();
        $handler  = TestAsset::responder();

        $pipeline->pipe($counter);
        $pipeline->pipe($counter);
        $pipeline->pipe($counter);

        $response1 = $pipeline->process(new ServerRequest(), $handler);
        $response2 = $pipeline->process(new ServerRequest(), $handler);

        $this->assertSame('3', (string) $response1->getBody());
        $this->assertSame('3', (string) $response2->getBody());
    }

    public function testCanBeUsedAndReusedAsRequestHandler()
    {
        $pipeline  = new MiddlewarePipeline();
        $counter   = TestAsset::counter();
        $responder = handlerToMiddleware(TestAsset::responder());

        $pipeline->pipe($counter);
        $pipeline->pipe($counter);
        $pipeline->pipe($counter);
        $pipeline->pipe($responder);
        $pipeline->pipe($counter); // This middleware should never be executed

        $response1 = $pipeline->handle(new ServerRequest());
        $response2 = $pipeline->handle(new ServerRequest());

        $this->assertSame('3', (string) $response1->getBody());
        $this->assertSame('3', (string) $response2->getBody());
    }

    public function testThrowsExceptionIfRequestReachesTheEndOfPipelineWithoutResponse()
    {
        $pipeline = new MiddlewarePipeline();
        $counter  = TestAsset::counter();

        $pipeline->pipe($counter);
        $pipeline->pipe($counter);
        $pipeline->pipe($counter);

        $this->expectException(RuntimeException::class);

        $pipeline->handle(new ServerRequest());
    }

    public function testThrowsExceptionIfRunningEmptyPipeline()
    {
        $pipeline = new MiddlewarePipeline();

        $this->expectException(RuntimeException::class);

        $pipeline->handle(new ServerRequest());
    }
}
