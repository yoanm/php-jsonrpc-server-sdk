<?php
namespace Yoanm\JsonRpcServer\Domain\Event\Acknowledge;

/**
 * Class OnBatchSubRequestProcessingEvent
 *
 * Dispatched only in case JSON-RPC call is a batch request, before that a sub request will be processed
 */
class OnBatchSubRequestProcessingEvent extends AbstractOnBatchSubRequestProcessEvent
{
    const EVENT_NAME = 'json_rpc_server_skd.on_batch_sub_request_processing';
}
