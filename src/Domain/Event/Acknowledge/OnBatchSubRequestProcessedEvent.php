<?php
namespace Yoanm\JsonRpcServer\Domain\Event\Acknowledge;

/**
 * Class OnBatchSubRequestProcessedEvent
 *
 * Dispatched only in case JSON-RPC call is a batch request, after that a sub request has been processed
 */
class OnBatchSubRequestProcessedEvent extends AbstractOnBatchSubRequestProcessEvent
{
    const EVENT_NAME = 'json_rpc_server_skd.on_batch_sub_request_processed';
}
