<?php
namespace Tests\Functional\Infra\Serialization\RawRequestSerializer;

trait RequestStringProviderTrait
{
    /**
     * @return array
     */
    public function provideValidRequestStringData()
    {
        $notificationRequest = [
            'json-rpc' => '2.0',
            'method' => 'default-method'
        ];
        $baseParamList = [
            'arg1' => 'value1',
            'arg-2' => 'val2',
        ];
        $baseNamedParamList = ['params' => $baseParamList];
        $baseNumericParamList = ['params' => array_values($baseParamList)];

        $baseRequestWithId = $notificationRequest + ['id' => uniqid()];
        $baseRequestWithIdAndNamedParams = $baseRequestWithId + $baseNamedParamList;
        $baseRequestWithIdAndParams = $baseRequestWithId + $baseNumericParamList;

        $baseNotificationWithNamedParams = $notificationRequest + $baseNamedParamList;
        $baseNotificationParams = $notificationRequest + $baseNumericParamList;

        return [
            'simple request without params' => [
                'content' => json_encode($baseRequestWithId),
                'isNotification' => false,
                'isBatch' => false,
            ],
            'simple request with named params' => [
                'content' => json_encode($baseRequestWithIdAndNamedParams),
                'isNotification' => false,
                'isBatch' => false,
            ],
            'simple request with numeric params' => [
                'content' => json_encode($baseRequestWithIdAndParams),
                'isNotification' => false,
                'isBatch' => false,
            ],
            'notification request without params' => [
                'content' => json_encode($notificationRequest),
                'isNotification' => true,
                'isBatch' => false,
            ],
            'notification request with named params' => [
                'content' => json_encode($baseNotificationWithNamedParams),
                'isNotification' => true,
                'isBatch' => false,
            ],
            'notification request with numeric params' => [
                'content' => json_encode($baseNotificationParams),
                'isNotification' => true,
                'isBatch' => false,
            ],
            'batch request ' => [
                'content' => json_encode([
                    $baseRequestWithIdAndNamedParams,
                    $baseNotificationWithNamedParams,
                    $baseNotificationParams,
                    $baseRequestWithId,
                ]),
                'isNotification' => false,
                'isBatch' => true,
            ],
        ];
    }
}
