<?php
namespace Tests\Functional\Infra\Serialization\RawResponseSerializer;

use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcCallResponse;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcResponse;

trait JsonRpcCallResponseProviderTrait
{
    /**
     * @return array
     */
    public function provideValidRawResponseData()
    {
        $baseNotificationResponse = (new JsonRpcResponse('2.0'))->setIsNotification(true);
        $baseNotificationResponse2 = (new JsonRpcResponse('2.0'))->setIsNotification(true);
        $baseResponse = (new JsonRpcResponse('2.0'))
            ->setId(uniqid())
            ->setResult('my-result');

        $baseResponse2 = (new JsonRpcResponse('2.0'))
            ->setId(uniqid())
            ->setResult('my-result2');

        $baseNotificationResponseOnError = (new JsonRpcResponse('2.0'))
            ->setIsNotification(true)
            ->setError(new JsonRpcException(124));

        $baseResponseOnError = (new JsonRpcResponse('2.0'))
            ->setId(uniqid())
            ->setError(new JsonRpcException(356));

        return [
            'simple response' => [
                'response' => (new JsonRpcCallResponse())->addResponse($baseResponse),
                'isBatch' => false,
                'expectNull' => false
            ],
            'simple response on error' => [
                'response' => (new JsonRpcCallResponse())->addResponse($baseResponseOnError),
                'isBatch' => false,
                'expectNull' => false
            ],
            'notification response' => [
                'response' => (new JsonRpcCallResponse())->addResponse($baseNotificationResponse),
                'isBatch' => false,
                'expectNull' => true
            ],
            'notification response on error' => [
                'response' => (new JsonRpcCallResponse())->addResponse($baseNotificationResponseOnError),
                'isBatch' => false,
                'expectNull' => true
            ],
            'batch response' => [
                'response' => (new JsonRpcCallResponse(true))
                    ->addResponse($baseResponse)
                    ->addResponse($baseNotificationResponse)
                    ->addResponse($baseNotificationResponse2)
                    ->addResponse($baseResponse2)
                ,
                'isBatch' => true,
                'expectNull' => false
            ],
            'batch response with some errors' => [
                'response' => (new JsonRpcCallResponse(true))
                    ->addResponse($baseResponse2)
                    ->addResponse($baseNotificationResponseOnError)
                    ->addResponse($baseNotificationResponse2)
                    ->addResponse($baseResponseOnError)
                ,
                'isBatch' => true,
                'expectNull' => false
            ],
            'batch response with only notification' => [
                'response' => (new JsonRpcCallResponse())
                    ->addResponse($baseNotificationResponse)
                    ->addResponse($baseNotificationResponse2)
                ,
                'isBatch' => true,
                'expectNull' => true
            ],
        ];
    }
}
