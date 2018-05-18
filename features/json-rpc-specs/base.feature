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

  Scenario: A batch call with only notifications but with sub requests on error should return an array with corresponding errors
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
    # Error should be because of :
    # - {"jsonrpc": "2.0"}
    # - "ABCDE"
    # - false
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

    ## From http://www.jsonrpc.org/specification#response_object->id
    ## "If there was an error in detecting the id in the Request object (e.g. Parse error/Invalid Request), it MUST be Null."
    Scenario: If there was a Parse error, id MUST be Null (even if provided)
      When I send following payload:
      """
      {
        "jsonrpc": "2.0",
        "id: "my-id"        !!invalid!!
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
  Scenario: If there was a Invalid Request, id MUST be Null (even if provided)
      When I send following payload:
      """
      {"id": "my-id"}
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
