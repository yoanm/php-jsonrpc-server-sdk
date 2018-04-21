Feature: Ensure JSON-RPC specifications
  Verify that each built-in errors are correctly managed

  Scenario: Parse error (-32700)
    When I send following payload:
    """
    {
      "jsonrpc": "2.0",!!invalid!!
    }
    """
    Then last response should be a valid json-rpc error
    And I should have the following response:
    """
    {
      "jsonrpc": "2.0",
      "id": null,
      "error": {
        "code": -32700,
        "message": "Parse error"
      }
    }
    """

  Scenario Outline: Invalid Request (-32600)
    When I send following payload:
    """
    <content>
    """
    Then last response should be a valid json-rpc error
    And I should have the following response:
    """
    {
      "jsonrpc": "2.0",
      "id": null,
      "error": {
        "code": -32600,
        "message": "Invalid request"
      }
    }
    """

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

  Scenario: Method not found (-32601)
    When I send following payload:
    """
    {
      "jsonrpc": "2.0",
      "id": "297c8498-5a54-471c-ac75-917be6435607",
      "method": "a-method-that-do-not-exist"
    }
    """
    Then last response should be a valid json-rpc error
    And I should have the following response:
    """
    {
      "jsonrpc": "2.0",
      "id": "297c8498-5a54-471c-ac75-917be6435607",
      "error": {
        "code": -32601,
        "message": "Method not found"
      }
    }
    """

  Scenario: Invalid params (-32602)
    When I send following payload:
    """
    {
      "jsonrpc": "2.0",
      "id": "297c8498-5a54-471c-ac75-917be6435607",
      "method": "method-that-throw-params-validation-exception"
    }
    """
    Then last response should be a valid json-rpc error
    And I should have the following response:
    """
    {
      "jsonrpc": "2.0",
      "id": "297c8498-5a54-471c-ac75-917be6435607",
      "error": {
        "code": -32602,
        "message": "Invalid params",
        "data": {
          "violations": [
            {
              "path": "path-on-error",
              "message": "method-that-throw-params-validation-exception validation exception"
            }
          ]
        }
      }
    }
    """

  Scenario: Internal error (-32603)
    When I send following payload:
    """
    {
      "jsonrpc": "2.0",
      "id": "297c8498-5a54-471c-ac75-917be6435607",
      "method": "method-that-throw-an-exception-during-execution"
    }
    """
    Then last response should be a valid json-rpc error
    And I should have the following response:
    """
    {
      "jsonrpc": "2.0",
      "id": "297c8498-5a54-471c-ac75-917be6435607",
      "error": {
        "code": -32603,
        "message": "Internal error",
        "data": {
          "previous": "method-that-throw-an-exception-during-execution execution exception"
        }
      }
    }
    """

  Scenario: Implementation-defined server-errors (-32099 to -32000)
    When I send following payload:
    """
    {
      "jsonrpc": "2.0",
      "id": "297c8498-5a54-471c-ac75-917be6435607",
      "method": "method-that-throw-a-custom-jsonrpc-exception-during-execution"
    }
    """
    Then last response should be a valid json-rpc error
    And I should have the following response:
    """
    {
      "jsonrpc": "2.0",
      "id": "297c8498-5a54-471c-ac75-917be6435607",
      "error": {
        "code": -32012,
        "message": "A custom json-rpc error",
        "data": {
          "custom-data-property": "custom-data-value"
        }
      }
    }
    """
