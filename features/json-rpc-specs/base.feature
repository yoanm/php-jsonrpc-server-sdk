Feature: Basic checks

  Scenario: A notification should have en empty response
    When I send following payload:
    """
    {
      "jsonrpc": "2.0",
      "method": "basic-method"
    }
    """
    Then I should have an empty response

  Scenario: A notification should have en empty response even on error
    When I send following payload:
    """
    {
      "jsonrpc": "2.0",
      "method": "a-method-that-do-not-exist"
    }
    """
    Then I should have an empty response

  Scenario: A batch call with only notifications should have en empty response
    When I send following payload:
    """
    [
      {
        "jsonrpc": "2.0",
        "method": "basic-method"
      },
      {
        "jsonrpc": "2.0",
        "method": "basic-method-with-parameters",
        "params": [1, 2, 3]
      }
    ]
    """
    Then I should have an empty response

  Scenario: A batch call with only notifications but with sub request on error should return an array with corresponding errors
    When I send following payload:
    """
    [
      {
        "jsonrpc": "2.0",
        "method": "basic-method"
      },
      {"jsonrpc": "2.0"},
      {
        "jsonrpc": "2.0",
        "method": "basic-method-with-parameters",
        "params": [1, 2, 3]
      },
      "ABCDE",
      false,
      {
        "jsonrpc": "2.0",
        "method": "a-method-that-do-not-exist"
      }
    ]
    """
    Then I should have the following response:
    """
    [
      {
        "jsonrpc": "2.0",
        "id": null,
        "error": {
          "code": -32600,
          "message": "Invalid request"
        }
      },
      {
        "jsonrpc": "2.0",
        "id": null,
        "error": {
          "code": -32600,
          "message": "Invalid request"
        }
      },
      {
        "jsonrpc": "2.0",
        "id": null,
        "error": {
          "code": -32600,
          "message": "Invalid request"
        }
      }
    ]
    """
