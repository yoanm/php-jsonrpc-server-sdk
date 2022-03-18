<?php
namespace Yoanm\JsonRpcServer\App\Serialization;

use Exception;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcInvalidRequestException;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcParseErrorException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCall;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCallResponse;

/**
 * Class JsonRpcCallSerializer
 */
class JsonRpcCallSerializer
{
    /** @var JsonRpcCallDenormalizer */
    private $callDenormalizer;
    /** @var JsonRpcCallResponseNormalizer */
    private $callResponseNormalizer;

    /**
     * @param JsonRpcCallDenormalizer       $callDenormalizer
     * @param JsonRpcCallResponseNormalizer $callResponseNormalizer
     */
    public function __construct(
        JsonRpcCallDenormalizer $callDenormalizer,
        JsonRpcCallResponseNormalizer $callResponseNormalizer
    ) {
        $this->callDenormalizer = $callDenormalizer;
        $this->callResponseNormalizer = $callResponseNormalizer;
    }

    /**
     * @param string $content
     *
     * @return JsonRpcCall
     *
     * @throws JsonRpcInvalidRequestException
     * @throws JsonRpcParseErrorException
     * @throws Exception
     */
    public function deserialize(string $content) : JsonRpcCall
    {
        return $this->denormalize(
            $this->decode($content)
        );
    }

    /**
     * @param JsonRpcCallResponse $jsonRpcCallResponse
     *
     * @return string
     */
    public function serialize(JsonRpcCallResponse $jsonRpcCallResponse) : string
    {
        return $this->encode(
            $this->normalize($jsonRpcCallResponse)
        );
    }

    /**
     * @param mixed $normalizedContent Could be an array or null for instance
     *
     * @return string
     */
    public function encode($normalizedContent) : string
    {
        $result = json_encode($normalizedContent);
        if (false === $result) {
            throw new JsonRpcException(
                json_last_error(),
                sprintf(
                    'Error during call encoding : %s',
                    json_last_error_msg()
                ),
                is_array($normalizedContent) ? $normalizedContent : [$normalizedContent]
            );
        }

        return $result;
    }

    /**
     * @param string $requestContent
     *
     * @return array<mixed> Decoded content
     *
     * @throws JsonRpcParseErrorException
     * @throws JsonRpcInvalidRequestException
     */
    public function decode(string $requestContent) : array
    {
        $decodedContent = \json_decode($requestContent, true);

        // Check if parsing is ok => Parse error
        if (\JSON_ERROR_NONE !== \json_last_error()) {
            throw new JsonRpcParseErrorException($requestContent, \json_last_error(), json_last_error_msg());
        }

        // Content must be either an array (normal request) or an array of array (batch request)
        //  => so must be an array
        // In case it's a batch call, at least one sub request must exist
        // and in case not, some required properties must exist
        // => array must have at least one child
        if (!is_array($decodedContent) || count($decodedContent) === 0) {
            throw new JsonRpcInvalidRequestException($requestContent);
        }

        return $decodedContent;
    }

    /**
     * @param array<mixed> $decodedContent
     *
     * @return JsonRpcCall
     *
     * @throws Exception
     */
    public function denormalize(array $decodedContent) : JsonRpcCall
    {
        return $this->callDenormalizer->denormalize($decodedContent);
    }

    /**
     * @param JsonRpcCallResponse $jsonRpcCallResponse
     *
     * @return array<mixed>|null
     */
    public function normalize(JsonRpcCallResponse $jsonRpcCallResponse) : ?array
    {
        return $this->callResponseNormalizer->normalize($jsonRpcCallResponse);
    }
}
