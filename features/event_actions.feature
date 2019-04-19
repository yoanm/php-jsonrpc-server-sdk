Feature: Actions through events

  Background:
    Given endpoint will use default JsonRpcServerDispatcher

  Scenario: Update result thanks to OnMethodSuccess event
    Given I will replace "Action\OnMethodSuccess" result by following json:
    """
    {"key": "my-custom-result"}
    """
    When I send following payload:
    """
    {
      "jsonrpc": "2.0",
      "id": 1,
      "method": "basic-method"
    }
    """
    Then I should have the following response:
    """
    {
      "jsonrpc": "2.0",
      "id": 1,
      "result": {"key": "my-custom-result"}
    }
    """

  Scenario: Update Exception thanks to OnException event
    Given I will replace "Action\OnException" exception by an exception with following message:
    """
    my custom exception message
    """
    When I send following payload:
    """
    {
      !!invalid JSON string!!
    }
    """
    Then I should have the following response:
    """
    {
      "jsonrpc": "2.0",
      "id": null,
      "error": {
        "code": -32603,
        "message": "Internal error",
        "data": {
          "previous": "my custom exception message"
        }
      }
    }
    """

  Scenario: Update Exception with custom JSON-RPC error thanks to OnException event
    Given I will replace "Action\OnException" exception by a "-32050" JSON-RPC exception with following message:
    """
    my custom exception message
    """
    When I send following payload:
    """
    {
      !!invalid JSON string!!
    }
    """
    Then I should have the following response:
    """
    {
      "jsonrpc": "2.0",
      "id": null,
      "error": {
        "code": -32050,
        "message": "my custom exception message"
      }
    }
    """

  Scenario: Update exception thanks to OnMethodFailure event
    Given I will replace "Action\OnMethodFailure" exception by an exception with following message:
    """
    my custom exception message
    """
    When I send following payload:
    """
    {
      "jsonrpc": "2.0",
      "id": 1,
      "method": "method-that-throw-an-exception-during-execution"
    }
    """
    Then I should have the following response:
    """
    {
      "jsonrpc": "2.0",
      "id": 1,
      "error": {
        "code": -32603,
        "message": "Internal error",
        "data": {
          "previous": "my custom exception message"
        }
      }
    }
    """

  Scenario: Update method exception with custom JSON-RPC error thanks to OnMethodFailure event
    Given I will replace "Action\OnMethodFailure" exception by a "-32020" JSON-RPC exception with following message:
    """
    my custom exception message
    """
    When I send following payload:
    """
    {
      "jsonrpc": "2.0",
      "id": 1,
      "method": "method-that-throw-an-exception-during-execution"
    }
    """
    Then I should have the following response:
    """
    {
      "jsonrpc": "2.0",
      "id": 1,
      "error": {
        "code": -32020,
        "message": "my custom exception message"
      }
    }
    """
