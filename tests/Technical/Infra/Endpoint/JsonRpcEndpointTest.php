<?php
namespace Tests\Technical\Infra\Endpoint;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Handler\ExceptionHandler;
use Yoanm\JsonRpcServer\App\Handler\JsonRpcRequestHandler;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCallResponse;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;

/**
 * @covers \Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint
 *
 * @group JsonRpcEndpoint
 */
class JsonRpcEndpointTest extends TestCase
{
    use ProphecyTrait;

    /** @var JsonRpcEndpoint */
    private $endpoint;
    /** @var JsonRpcCallSerializer|ObjectProphecy */
    private $jsonRpcCallSerializer;
    /** @var JsonRpcRequestHandler|ObjectProphecy */
    private $jsonRpcRequestHandler;
    /** @var ExceptionHandler|ObjectProphecy */
    private $exceptionHandler;

    protected function setUp(): void
    {
        $this->jsonRpcCallSerializer = $this->prophesize(JsonRpcCallSerializer::class);
        $this->jsonRpcRequestHandler = $this->prophesize(JsonRpcRequestHandler::class);
        $this->exceptionHandler = $this->prophesize(ExceptionHandler::class);

        $this->endpoint = new JsonRpcEndpoint(
            $this->jsonRpcCallSerializer->reveal(),
            $this->jsonRpcRequestHandler->reveal(),
            $this->exceptionHandler->reveal()
        );
    }

    /**
     * @dataProvider provideHandleExceptionData
     *
     * @param string $exceptionClass
     */
    public function testShouldManageAnyExceptionThrownDuringRequestDeserialization($exceptionClass)
    {
        $requestString = 'request-string';
        $expectedResponseString = 'expected-response-string';

        $fakeException = $this->prophesize($exceptionClass);
        /** @var ObjectProphecy|JsonRpcResponse $fakeResponseItem */
        $fakeResponseItem = $this->prophesize(JsonRpcResponse::class);

        $this->jsonRpcCallSerializer->deserialize($requestString)
            ->willThrow($fakeException->reveal())
            ->shouldBeCalled();

        $this->exceptionHandler->getJsonRpcResponseFromException($fakeException->reveal())
            ->willReturn($fakeResponseItem->reveal())
            ->shouldBeCalled();

        $this->jsonRpcCallSerializer->serialize(Argument::allOf(
            Argument::type(JsonRpcCallResponse::class),
            Argument::which('getResponseList', [$fakeResponseItem->reveal()])
        ))
            ->willReturn($expectedResponseString)
            ->shouldBeCalled();

        $this->assertSame(
            $expectedResponseString,
            $this->endpoint->index($requestString)
        );
    }

    /**
     * @dataProvider provideHandleExceptionData
     *
     * @param string $exceptionClass
     */
    public function testShouldManageAnyExceptionThrownDuringItemProcessing($exceptionClass)
    {
        $requestString = 'request-string';
        $expectedResponseString = 'expected-response-string';

        /** @var ObjectProphecy|JsonRpcRequest $fakeRequestItem */
        $fakeRequestItem = $this->prophesize(JsonRpcRequest::class);
        $fakeException = $this->prophesize($exceptionClass);
        /** @var ObjectProphecy|JsonRpcResponse $fakeResponseItem */
        $fakeResponseItem = $this->prophesize(JsonRpcResponse::class);
        /** @var JsonRpcCall $jsonRpcCall */
        $jsonRpcCall = (new JsonRpcCall(false))
            ->addRequestItem($fakeRequestItem->reveal())
        ;

        $this->jsonRpcCallSerializer->deserialize($requestString)
            ->willReturn($jsonRpcCall)
            ->shouldBeCalled();

        $this->jsonRpcRequestHandler->processJsonRpcRequest($fakeRequestItem->reveal())
            ->willThrow($fakeException->reveal())
            ->shouldBeCalled();

        $this->exceptionHandler->getJsonRpcResponseFromException(
            $fakeException->reveal(),
            $fakeRequestItem->reveal()
        )
            ->willReturn($fakeResponseItem->reveal())
            ->shouldBeCalled();

        $this->jsonRpcCallSerializer->serialize(Argument::allOf(
            Argument::type(JsonRpcCallResponse::class),
            Argument::which('getResponseList', [$fakeResponseItem->reveal()])
        ))
            ->willReturn($expectedResponseString)
            ->shouldBeCalled();

        $this->assertSame(
            $expectedResponseString,
            $this->endpoint->index($requestString)
        );
    }

    /**
     * @dataProvider provideHandleExceptionData
     *
     * @param string $exceptionClass
     */
    public function testShouldManageAnyExceptionThrownDuringResponseSerialization($exceptionClass)
    {
        $requestString = 'request-string';
        $expectedResponseString = 'expected-response-string';

        /** @var ObjectProphecy|JsonRpcRequest $fakeRequestItem */
        $fakeRequestItem = $this->prophesize(JsonRpcRequest::class);
        $fakeException = $this->prophesize($exceptionClass);
        /** @var ObjectProphecy|JsonRpcResponse $fakeResponseItem */
        $fakeResultResponseItem = $this->prophesize(JsonRpcResponse::class);
        /** @var ObjectProphecy|JsonRpcResponse $fakeResponseItem */
        $fakeExceptionResponseItem = $this->prophesize(JsonRpcResponse::class);
        /** @var JsonRpcCall $jsonRpcCall */
        $jsonRpcCall = (new JsonRpcCall(false))
            ->addRequestItem($fakeRequestItem->reveal())
        ;

        $this->jsonRpcCallSerializer->deserialize($requestString)
            ->willReturn($jsonRpcCall)
            ->shouldBeCalled();

        $this->jsonRpcRequestHandler->processJsonRpcRequest($fakeRequestItem->reveal())
            ->willReturn($fakeResultResponseItem->reveal())
            ->shouldBeCalled();

        $this->jsonRpcCallSerializer->serialize(Argument::allOf(
            Argument::type(JsonRpcCallResponse::class),
            Argument::which('getResponseList', [$fakeResultResponseItem->reveal()])
        ))
            ->willThrow($fakeException->reveal())
            ->shouldBeCalled();

        $this->exceptionHandler->getJsonRpcResponseFromException($fakeException->reveal())
            ->willReturn($fakeExceptionResponseItem->reveal())
            ->shouldBeCalled();

        $this->jsonRpcCallSerializer->serialize(Argument::allOf(
            Argument::type(JsonRpcCallResponse::class),
            Argument::which('getResponseList', [$fakeExceptionResponseItem->reveal()])
        ))
            ->willReturn($expectedResponseString)
            ->shouldBeCalled();

        $this->assertSame(
            $expectedResponseString,
            $this->endpoint->index($requestString)
        );
    }

    public function provideHandleExceptionData()
    {
        return [
            'default' => [
                'exceptionClass' => \Exception::class,
            ],
            'JsonRpcException' => [
                'exceptionClass' => JsonRpcException::class,
            ],
        ];
    }
}
