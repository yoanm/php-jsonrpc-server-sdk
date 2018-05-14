<?php
namespace Yoanm\JsonRpcServer\Domain;

use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;

/**
 * Class JsonRpcServerDispatcherInterface
 */
interface JsonRpcServerDispatcherInterface
{
    /*** In and out events ***/
    // Sent when a request has been passed to the endpoint and successfully deserialized
    const ON_REQUEST_RECEIVED_EVENT_NAME             = 'json_rpc_server_skd.on_request_received';
    // Sent when a response has been successfully serialized by the endpoint and will be returned
    const ON_RESPONSE_SENDING_EVENT_NAME             = 'json_rpc_server_skd.on_response_sending';

    /*** Events related to JSON-RPC methods ***/
    // Sent before JSON-RPC will be called, in order to validate params
    const VALIDATE_PARAMS_EVENT_NAME                 = 'json_rpc_server_skd.validate_params';
    // Sent, only in case JSON-RPC method return a response.
    const ON_METHOD_SUCCESS_EVENT_NAME               = 'json_rpc_server_skd.on_method_success';
    // Sent, only in case JSON-RPC method thrown an exception.
    const ON_METHOD_FAILURE_EVENT_NAME               = 'json_rpc_server_skd.on_method_failure';

    /*** Events related to JSON-RPC batch call ***/
    // Sent, only in case JSON-RPC call is a batch request, before that a sub request will be processed
    const ON_BATCH_SUB_REQUEST_PROCESSING_EVENT_NAME = 'json_rpc_server_skd.on_batch_sub_request_processing';
    // Sent, only in case JSON-RPC call is a batch request, after that a sub request has been processed
    const ON_BATCH_SUB_REQUEST_PROCESSED_EVENT_NAME  = 'json_rpc_server_skd.on_batch_sub_request_processed';

    /** Exception event */
    // Sent when a response has been successfully serialized by the endpoint and will be returned
    const ON_EXCEPTION_EVENT_NAME                    = 'json_rpc_server_skd.on_exception';


    /**
     * @param JsonRpcServerEvent $event
     */
    public function dispatchJsonRpcEvent(string $eventName, JsonRpcServerEvent $event = null);

    /**
     * @param callable $listener
     * @param string   $targetEventClassName
     * @param int      $priority
     */
    public function addJsonRpcListener(string $eventName, $listener);
}
