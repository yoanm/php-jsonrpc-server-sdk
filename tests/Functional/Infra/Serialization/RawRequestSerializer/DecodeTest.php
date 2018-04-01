<?php
namespace Tests\Functional\Infra\Serialization\RawRequestSerializer;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Serialization\RequestDenormalizer;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidRequestException;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcParseErrorException;
use Yoanm\JsonRpcServer\Infra\Serialization\RawRequestSerializer;

/**
 * @covers \Yoanm\JsonRpcServer\Infra\Serialization\RawRequestSerializer
 */
class DecodeTest extends TestCase
{
    use RequestStringProviderTrait;

    /** @var RawRequestSerializer */
    private $rawRequestSerializer;
    /** @var RequestDenormalizer|ObjectProphecy */
    private $requestDenormalizer;

    protected function setUp()
    {
        $this->requestDenormalizer = $this->prophesize(RequestDenormalizer::class);
        $this->rawRequestSerializer = new RawRequestSerializer(
            $this->requestDenormalizer->reveal()
        );
    }

    /**
     * @dataProvider provideValidRequestString
     *
     * @param string $content
     */
    public function testShouldHandle($content)
    {
        $this->assertTrue(is_array(
            $this->rawRequestSerializer->decode($content)
        ));
    }

    public function testShouldThrowProperErrorInCaseOfParseError()
    {
        $this->expectException(JsonRpcParseErrorException::class);

        $this->rawRequestSerializer->decode('not-a-json;1:2")');
    }

    /**
     * @dataProvider provideNotAnArrayRequestStringData
     *
     * @param string $invalidContent
     */
    public function testShouldThrowProperErrorIfDecodedContentIs($invalidContent)
    {
        $this->expectException(JsonRpcInvalidRequestException::class);

        $this->rawRequestSerializer->decode($invalidContent);
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
        ];
    }
}
