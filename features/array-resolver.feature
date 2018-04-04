Feature: Array method Resolver

  Scenario: Should return the requested method
    Given I add "getDummy" JSON-RPC method to ArrayMethodResolver
    When ArrayMethodResolver resolve "getDummy"
    Then ArrayMethodResolver result should be "getDummy" JSON-RPC method

  Scenario: Should return null if requested method does not exist
    When ArrayMethodResolver resolve "not-existing-method"
    Then ArrayMethodResolver result should be a null JSON-RPC method
