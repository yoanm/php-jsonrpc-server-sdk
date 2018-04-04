@symfony-extension
Feature: Symfony extension

  Scenario: An endpoint should be quickly usable
    Given I process the symfony extension
    When I load endpoint from "yoanm.jsonrpc_server_sdk.endpoint" service
    And I inject my "my-method-name" JSON-RPC method into default method resolver instance
    And I inject my "an-another-method" JSON-RPC method into default method resolver instance
    And I inject my "getDummy" JSON-RPC method into default method resolver instance
    Then endpoint should respond to following JSON-RPC methods:
      | getDummy          |
      | my-method-name    |
      | an-another-method |

  Scenario: An endpoint should be quickly usable also by using container injection
    Given I process the symfony extension
    And I inject my "my-method-name" JSON-RPC method into default method resolver definition
    And I inject my "an-another-method" JSON-RPC method into default method resolver definition
    And I inject my "getDummy" JSON-RPC method into default method resolver definition
    When I load endpoint from "yoanm.jsonrpc_server_sdk.endpoint" service
    Then endpoint should respond to following JSON-RPC methods:
      | getDummy          |
      | my-method-name    |
      | an-another-method |

    @symfony-method-resolver-tag
  Scenario: Use a custom method resolver
    Given I tag my custom method resolver service with "yoanm.jsonrpc_server_sdk.method_resolver"
    And I process the symfony extension
    When I load endpoint from "yoanm.jsonrpc_server_sdk.endpoint" service
    And I inject my "doSomething" JSON-RPC method into my custom method resolver instance
    And I inject my "doAnotherThing" JSON-RPC method into my custom method resolver instance
    And I inject my "doALastThing" JSON-RPC method into my custom method resolver instance
    Then endpoint should respond to following JSON-RPC methods:
      | doAnotherThing |
      | doALastThing   |
      | doSomething    |

    @symfony-method-resolver-tag
  Scenario: Use a custom method resolver with json-rpc methods autoloading
    Given I tag my custom method resolver service with "yoanm.jsonrpc_server_sdk.method_resolver"
    And I process the symfony extension
    And I inject my "doSomething" JSON-RPC method into my custom method resolver definition
    And I inject my "doAnotherThing" JSON-RPC method into my custom method resolver definition
    And I inject my "doALastThing" JSON-RPC method into my custom method resolver definition
    When I load endpoint from "yoanm.jsonrpc_server_sdk.endpoint" service
    Then endpoint should respond to following JSON-RPC methods:
      | doAnotherThing |
      | doALastThing   |
      | doSomething    |

    @symfony-jsonrpc-method-tag
  Scenario: Define json-rpc method with tags
    Given I have a JSON-RPC method service definition with "yoanm.jsonrpc_server_sdk.jsonrpc_method" tag and following tag attributes:
    """
    {"method": "my-method-name"}
    """
    And I have a JSON-RPC method service definition with "yoanm.jsonrpc_server_sdk.jsonrpc_method" tag and following tag attributes:
    """
    {"method": "an-another-method"}
    """
    And I have a JSON-RPC method service definition with "yoanm.jsonrpc_server_sdk.jsonrpc_method" tag and following tag attributes:
    """
    {"method": "getDummy"}
    """
    And I process the symfony extension
    When I load endpoint from "yoanm.jsonrpc_server_sdk.endpoint" service
    Then endpoint should respond to following JSON-RPC methods:
    | getDummy          |
    | my-method-name          |
    | an-another-method |
