Feature: Ensure JSON-RPC specifications
  See http://www.jsonrpc.org/specification#examples

  Scenario: rpc call with positional parameters
    When I send following payload:
    """
    {"jsonrpc": "2.0", "method": "basic-method-with-params", "params": [42, 23], "id": 1}
    """
    Then I should have the following response:
    """
    {"jsonrpc": "2.0", "result": "basic-method-with-params-result", "id": 1}
    """

  Scenario: rpc call with named parameters
    When I send following payload:
    """
    {"jsonrpc": "2.0", "method": "basic-method-with-params", "params": {"subtrahend": 23, "minuend": 42}, "id": 3}
    """
    Then I should have the following response:
    """
    {"jsonrpc": "2.0", "result": "basic-method-with-params-result", "id": 3}
    """

  Scenario: a Notification
    When I send following payload:
    """
    {"jsonrpc": "2.0", "method": "basic-method-with-params", "params": [1,2,3,4,5]}
    """
    Then I should have an empty response

  Scenario: rpc call of non-existent method
    When I send following payload:
    """
    {"jsonrpc": "2.0", "method": "foobar", "id": "1"}
    """
    Then I should have the following response:
    """
    {"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "1"}
    """

  Scenario: rpc call with invalid JSON
    When I send following payload:
    """
    {"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]
    """
    Then I should have the following response:
    """
    {"jsonrpc": "2.0", "error": {"code": -32700, "message": "Parse error"}, "id": null}
    """

  Scenario: rpc call with invalid Request object
    When I send following payload:
    """
    {"jsonrpc": "2.0", "method": 1, "params": "bar"}
    """
    Then I should have the following response:
    """
    {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid request"}, "id": null}
    """

  Scenario: rpc call Batch, invalid JSON
    When I send following payload:
    """
    [
      {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
      {"jsonrpc": "2.0", "method"
    ]
    """
    Then I should have the following response:
    """
    {"jsonrpc": "2.0", "error": {"code": -32700, "message": "Parse error"}, "id": null}
    """

  Scenario: rpc call with an empty Array
    When I send following payload:
    """
    []
    """
    Then I should have the following response:
    """
    {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid request"}, "id": null}
    """

  Scenario: rpc call with an invalid Batch (but not empty)
    When I send following payload:
    """
    [1]
    """
    Then I should have the following response:
    """
    [
      {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid request"}, "id": null}
    ]
    """

  Scenario: rpc call with invalid Batch
    When I send following payload:
    """
    [1,2,3]
    """
    Then I should have the following response:
    """
    [
      {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid request"}, "id": null},
      {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid request"}, "id": null},
      {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid request"}, "id": null}
    ]
    """

  Scenario: rpc call Batch
    When I send following payload:
    """
    [
        {"jsonrpc": "2.0", "method": "basic-method-with-params", "params": [1,2,4], "id": "1"},
        {"jsonrpc": "2.0", "method": "basic-method"},
        {"jsonrpc": "2.0", "method": "basic-method-with-params", "params": [42,23], "id": "2"},
        {"foo": "boo"},
        {"jsonrpc": "2.0", "method": "foo.get", "params": {"name": "myself"}, "id": "5"},
        {"jsonrpc": "2.0", "method": "basic-method", "id": "9"}
    ]
    """
    Then I should have the following response:
    """
    [
      {"jsonrpc": "2.0", "result": "basic-method-with-params-result", "id": "1"},
      {"jsonrpc": "2.0", "result": "basic-method-with-params-result", "id": "2"},
      {"jsonrpc": "2.0", "error": {"code": -32600, "message": "Invalid request"}, "id": null},
      {"jsonrpc": "2.0", "error": {"code": -32601, "message": "Method not found"}, "id": "5"},
      {"jsonrpc": "2.0", "result": "basic-method-result", "id": "9"}
    ]
    """
