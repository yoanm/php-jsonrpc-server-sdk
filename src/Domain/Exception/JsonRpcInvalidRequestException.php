<?php
namespace Yoanm\JsonRpcServer\Domain\Exception;

/**
 * Class JsonRpcInvalidRequestException
 */
class JsonRpcInvalidRequestException extends JsonRpcException
{
    const CODE = -32600;

    const CONTENT_KEY = 'content';
    const DESCRIPTION_KEY = 'description';

    /**
     * @param mixed  $content Request parsed content
     * @param string $message Optional description
     */
    public function __construct($content, string $description = null)
    {
        $data = [self::CONTENT_KEY => $content];
        if ($description) {
            $data[self::DESCRIPTION_KEY] = $description;
        }
        parent::__construct(self::CODE, 'Parse error', $data);
    }
}
