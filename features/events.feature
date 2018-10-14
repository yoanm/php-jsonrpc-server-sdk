Feature: Events dispatched

  Background:
    Given endpoint will use default JsonRpcServerDispatcher

  Scenario: Simple request
    When I send following payload:
    """
    {
      "jsonrpc": "2.0",
      "id": 1,
      "method": "basic-method"
    }
    """
    Then 3 events should have been dispatched
    And a "Acknowledge\OnRequestReceived" event named "json_rpc_server_skd.on_request_received" should have been dispatched
    And a "Action\OnMethodSuccess" event named "json_rpc_server_skd.on_method_success" should have been dispatched
    And a "Acknowledge\OnResponseSending" event named "json_rpc_server_skd.on_response_sending" should have been dispatched

  Scenario: Batch request
    When I send following payload:
    """
    [
      {
        "jsonrpc": "2.0",
        "id": 1,
        "method": "basic-method"
      },
      {
        "jsonrpc": "2.0",
        "id": 2,
        "method": "basic-method-with-params",
        "params": [1, 2, 3]
      },

      {
        "jsonrpc": "2.0",
        "id": 3,
        "method": "basic-method"
      }
    ]
    """
    Then 11 events should have been dispatched
    And a "Acknowledge\OnRequestReceived" event named "json_rpc_server_skd.on_request_received" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessing" event named "json_rpc_server_skd.on_batch_sub_request_processing" should have been dispatched
    And a "Action\OnMethodSuccess" event named "json_rpc_server_skd.on_method_success" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessed" event named "json_rpc_server_skd.on_batch_sub_request_processed" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessing" event named "json_rpc_server_skd.on_batch_sub_request_processing" should have been dispatched
    And a "Action\OnMethodSuccess" event named "json_rpc_server_skd.on_method_success" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessed" event named "json_rpc_server_skd.on_batch_sub_request_processed" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessing" event named "json_rpc_server_skd.on_batch_sub_request_processing" should have been dispatched
    And a "Action\OnMethodSuccess" event named "json_rpc_server_skd.on_method_success" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessed" event named "json_rpc_server_skd.on_batch_sub_request_processed" should have been dispatched
    And a "Acknowledge\OnResponseSending" event named "json_rpc_server_skd.on_response_sending" should have been dispatched

  Scenario Outline: Request on exception
    When I send following payload:
    """
    <content>
    """
    Then 2 event should have been dispatched
    And a "Action\OnException" event named "json_rpc_server_skd.on_exception" should have been dispatched
    And a "Acknowledge\OnResponseSending" event named "json_rpc_server_skd.on_response_sending" should have been dispatched

  Examples:
  | content                                                  |
    # Empty batch call
  | []                                                       |
    # Request without any parameter
  | {}                                                       |
    # No jsonrpc property
  | {"method": "my-method"}                                  |
    # No method property
  | {"jsonrpc": "2.0"}                                       |
    # Params property not an array or object
  | {"jsonrpc": "2.0", "method": "my-method", "params": 234} |

  Scenario: Batch request on exception (invalid payload)
    When I send following payload:
    """
    [1, 2, 3]
    """
    Then 11 events should have been dispatched
    And a "Acknowledge\OnRequestReceived" event named "json_rpc_server_skd.on_request_received" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessing" event named "json_rpc_server_skd.on_batch_sub_request_processing" should have been dispatched
    And a "Action\OnException" event named "json_rpc_server_skd.on_exception" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessed" event named "json_rpc_server_skd.on_batch_sub_request_processed" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessing" event named "json_rpc_server_skd.on_batch_sub_request_processing" should have been dispatched
    And a "Action\OnException" event named "json_rpc_server_skd.on_exception" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessed" event named "json_rpc_server_skd.on_batch_sub_request_processed" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessing" event named "json_rpc_server_skd.on_batch_sub_request_processing" should have been dispatched
    And a "Action\OnException" event named "json_rpc_server_skd.on_exception" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessed" event named "json_rpc_server_skd.on_batch_sub_request_processed" should have been dispatched
    And a "Acknowledge\OnResponseSending" event named "json_rpc_server_skd.on_response_sending" should have been dispatched

  Scenario: Method failure on simple request
    When I send following payload:
    """
    {
      "jsonrpc": "2.0",
      "id": 1,
      "method": "method-that-throw-an-exception-during-execution"
    }
    """
    Then 3 events should have been dispatched
    And a "Acknowledge\OnRequestReceived" event named "json_rpc_server_skd.on_request_received" should have been dispatched
    And a "Action\OnMethodFailure" event named "json_rpc_server_skd.on_method_failure" should have been dispatched
    And a "Acknowledge\OnResponseSending" event named "json_rpc_server_skd.on_response_sending" should have been dispatched

  Scenario: Method failure on batch request
    When I send following payload:
    """
    [
      {
        "jsonrpc": "2.0",
        "id": 1,
        "method": "method-that-throw-an-exception-during-execution"
      },
      {
        "jsonrpc": "2.0",
        "id": 2,
        "method": "method-that-throw-an-exception-during-execution",
        "params": [1, 2, 3]
      },

      {
        "jsonrpc": "2.0",
        "id": 3,
        "method": "method-that-throw-an-exception-during-execution"
      }
    ]
    """
    Then 11 events should have been dispatched
    And a "Acknowledge\OnRequestReceived" event named "json_rpc_server_skd.on_request_received" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessing" event named "json_rpc_server_skd.on_batch_sub_request_processing" should have been dispatched
    And a "Action\OnMethodFailure" event named "json_rpc_server_skd.on_method_failure" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessed" event named "json_rpc_server_skd.on_batch_sub_request_processed" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessing" event named "json_rpc_server_skd.on_batch_sub_request_processing" should have been dispatched
    And a "Action\OnMethodFailure" event named "json_rpc_server_skd.on_method_failure" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessed" event named "json_rpc_server_skd.on_batch_sub_request_processed" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessing" event named "json_rpc_server_skd.on_batch_sub_request_processing" should have been dispatched
    And a "Action\OnMethodFailure" event named "json_rpc_server_skd.on_method_failure" should have been dispatched
    And a "Acknowledge\OnBatchSubRequestProcessed" event named "json_rpc_server_skd.on_batch_sub_request_processed" should have been dispatched
    And a "Acknowledge\OnResponseSending" event named "json_rpc_server_skd.on_response_sending" should have been dispatched
