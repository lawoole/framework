<?php
namespace Tests\Homer\Calling;

use Lawoole\Homer\Calling\Invokers\Invoker;
use Lawoole\Homer\Calling\ProxyFactory;
use Lawoole\Homer\Calling\Result;
use Mockery;
use PHPUnit\Framework\TestCase;

class ProxyFactoryTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testCreateProxy()
    {
        $invoker = Mockery::mock(Invoker::class);
        $invoker->shouldReceive('getInterface')->andReturn(ProxyTestServiceInterface::class);
        $invoker->shouldReceive('invoke')->andReturnUsing(function ($invocation) {
            return new Result($invocation->getArguments());
        });

        $proxyFactory = new ProxyFactory;

        $proxy = $proxyFactory->proxy($invoker);

        $this->assertInstanceOf(ProxyTestServiceInterface::class, $proxy);

        return $proxy;
    }

    /**
     * @depends testCreateProxy
     *
     * @param $proxy
     */
    public function testProxyCalling($proxy)
    {
        $this->assertEquals([], $proxy->testEmptyArguments());

        $this->assertEquals(['a', 1, false], $proxy->testNormalArguments('a', 1, false));

        $this->assertEquals(['b', 2, true], $proxy->testVariadicArguments('b', 2, true));
    }
}

interface ProxyTestServiceInterface
{
    public function testEmptyArguments();

    public function testNormalArguments($one, $two, $three);

    public function testVariadicArguments($one, ...$arguments);
}