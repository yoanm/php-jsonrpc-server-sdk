<?php
namespace Tests\Functional\App\Serialization\JsonRpcCallSerializer;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallResponseNormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidRequestException;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcParseErrorException;

/**
 * @covers \Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer
 */
class DecodeTest extends TestCase
{
    use RequestStringProviderTrait;

    /** @var JsonRpcCallSerializer */
    private $jsonRpcCallSerializer;
    /** @var JsonRpcCallDenormalizer|ObjectProphecy */
    private $callDenormalizer;
    /** @var JsonRpcCallResponseNormalizer|ObjectProphecy */
    private $callResponseNormalizer;

    protected function setUp()
    {
        $this->callDenormalizer = $this->prophesize(JsonRpcCallDenormalizer::class);
        $this->callResponseNormalizer = $this->prophesize(JsonRpcCallResponseNormalizer::class);

        $this->jsonRpcCallSerializer = new JsonRpcCallSerializer(
            $this->callDenormalizer->reveal(),
            $this->callResponseNormalizer->reveal()
        );
    }

    /**
     * @dataProvider provideValidRequestStringData
     *
     * @param string $content
     */
    public function testShouldHandle($content)
    {
        $this->assertTrue(is_array(
            $this->jsonRpcCallSerializer->decode($content)
        ));
    }

    public function testShouldThrowProperErrorInCaseOfParseError()
    {
        $this->expectException(JsonRpcParseErrorException::class);

        $this->jsonRpcCallSerializer->decode('not-a-json;1:2")');
    }

    /**
     * @dataProvider provideNotAnArrayRequestStringData
     *
     * @param string $invalidContent
     */
    public function testShouldThrowProperErrorIfDecodedContentIs($invalidContent)
    {
        $this->expectException(JsonRpcInvalidRequestException::class);

        $this->jsonRpcCallSerializer->decode($invalidContent);
    }

    public function provideNotAnArrayRequestStringData()
    {
        return [
            'null' => [
                'invalidContent' => json_encode(null),
            ],
            'a boolean' => [
                'invalidContent' => json_encode(false),
            ],
            'a string' => [
                'invalidContent' => json_encode('test-string'),
            ],
            'an integer' => [
                'invalidContent' => json_encode(123),
            ],
            'an empty array' => [
                'invalidContent' => json_encode([]),
            ],
        ];
    }
}
