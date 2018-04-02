<?php
namespace Tests\Functional\Infra\Serialization\RawResponseSerializer;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Yoanm\JsonRpcServer\App\Serialization\ResponseNormalizer;
use Yoanm\JsonRpcServer\Infra\Serialization\RawResponseSerializer;

/**
 * @covers \Yoanm\JsonRpcServer\Infra\Serialization\RawResponseSerializer
 */
class EncodeTest extends TestCase
{
    /** @var RawResponseSerializer */
    private $rawResponseSerializer;
    /** @var ResponseNormalizer|ObjectProphecy */
    private $responseNormalizer;

    protected function setUp()
    {
        $this->responseNormalizer= $this->prophesize(ResponseNormalizer::class);
        $this->rawResponseSerializer = new RawResponseSerializer(
            $this->responseNormalizer->reveal()
        );
    }

    public function testShouldJsonEncodeContent()
    {
        $decoded = [
            'a' => 'b',
            'c' => [
                'd' => 'e',
                'f' => [
                    'g',
                    'h',
                    2,
                    true,
                    null
                ]
            ],
            'i' => 4,
            'j' => false,
            'k' => null
        ];

        $encoded = $this->rawResponseSerializer->encode($decoded);

        $this->assertSame(
            $encoded,
            json_encode($decoded)
        );
    }
}
