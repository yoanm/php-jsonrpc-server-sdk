<?php
namespace Tests\Functional\App\Serialization;

use PHPUnit\Framework\TestCase;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcResponseErrorNormalizer;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInternalErrorException;

/**
 * @covers \Yoanm\JsonRpcServer\App\Serialization\JsonRpcResponseErrorNormalizer
 *
 * @group JsonRpcResponseErrorNormalizer
 * @group Serialization
 */
class JsonRpcResponseErrorNormalizerTest extends TestCase
{
    protected function prepareException($exceptionMessage = 'Test exception', $exceptionCode = 12345) : \Throwable
    {
        try {
            // create a long stack trace
            $closure = function ($exceptionMessage, $exceptionCode) {
                throw new \RuntimeException($exceptionMessage, $exceptionCode);
            };

            call_user_func($closure, $exceptionMessage, $exceptionCode);
        } catch (\Throwable $exception) {
            // shutdown test exception as prepared
        }

        return $exception;
    }

    public function testShouldNormalizeError()
    {
        $normalizer = new JsonRpcResponseErrorNormalizer();

        $exceptionMessage = 'Test exception';
        $exceptionCode = 12345;

        $exception = $this->prepareException($exceptionMessage, $exceptionCode);

        $debugData = $normalizer->normalize(new JsonRpcInternalErrorException($exception));

        $this->assertFalse(empty($debugData['_class']));
        $this->assertFalse(empty($debugData['_code']));
        $this->assertFalse(empty($debugData['_message']));
        $this->assertFalse(empty($debugData['_trace']));

        $this->assertSame(get_class($exception), $debugData['_class']);
        $this->assertSame($exceptionMessage, $debugData['_message']);
        $this->assertSame($exceptionCode, $debugData['_code']);
    }

    /**
     * @depends testShouldNormalizeError
     */
    public function testShouldRestrictTraceSize()
    {
        $exception = $this->prepareException();
        $maxTraceSize = 1;

        $normalizer = new JsonRpcResponseErrorNormalizer($maxTraceSize);
        $debugData = $normalizer->normalize(new JsonRpcInternalErrorException($exception));

        $this->assertCount($maxTraceSize, $debugData['_trace']);
    }

    /**
     * @depends testShouldNormalizeError
     */
    public function testShouldNotDisplayTraceOnZeroSize()
    {
        $exception = $this->prepareException();

        $normalizer = new JsonRpcResponseErrorNormalizer(0);
        $debugData = $normalizer->normalize(new JsonRpcInternalErrorException($exception));

        $this->assertFalse(array_key_exists('_trace', $debugData));
    }

    /**
     * @depends testShouldRestrictTraceSize
     */
    public function testShouldShowTraceArguments()
    {
        $exception = $this->prepareException();

        $normalizer = new JsonRpcResponseErrorNormalizer(99, true);
        $debugData = $normalizer->normalize(new JsonRpcInternalErrorException($exception));

        $argsFound = false;
        foreach ($debugData['_trace'] as $entry) {
            if (isset($entry['args'])) {
                $argsFound = true;
                break;
            }
        }

        $this->assertTrue($argsFound);
    }

    /**
     * @depends testShouldRestrictTraceSize
     */
    public function testShouldHideTraceArguments()
    {
        $exception = $this->prepareException();

        $normalizer = new JsonRpcResponseErrorNormalizer(99, false);
        $debugData = $normalizer->normalize(new JsonRpcInternalErrorException($exception));

        $argsFound = false;
        foreach ($debugData['_trace'] as $entry) {
            if (isset($entry['args'])) {
                $argsFound = true;
                break;
            }
        }

        $this->assertFalse($argsFound);
    }
}