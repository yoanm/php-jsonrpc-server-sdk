# PHP JSON-RPC server sdk
[![License](https://img.shields.io/github/license/yoanm/php-jsonrpc-server-sdk.svg)](https://github.com/yoanm/php-jsonrpc-server-sdk)
[![Code size](https://img.shields.io/github/languages/code-size/yoanm/php-jsonrpc-server-sdk.svg)](https://github.com/yoanm/php-jsonrpc-server-sdk)
[![Dependabot Status](https://api.dependabot.com/badges/status?host=github&repo=yoanm/php-jsonrpc-server-sdk)](https://dependabot.com)

[![Scrutinizer Build Status](https://img.shields.io/scrutinizer/build/g/yoanm/php-jsonrpc-server-sdk.svg?label=Scrutinizer&logo=scrutinizer)](https://scrutinizer-ci.com/g/yoanm/php-jsonrpc-server-sdk/build-status/master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/yoanm/php-jsonrpc-server-sdk/master.svg?logo=scrutinizer)](https://scrutinizer-ci.com/g/yoanm/php-jsonrpc-server-sdk/?branch=master)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/yoanm/php-jsonrpc-server-sdk/master.svg?logo=scrutinizer)](https://scrutinizer-ci.com/g/yoanm/php-jsonrpc-server-sdk/?branch=master)



[![Latest Stable Version](https://img.shields.io/packagist/v/yoanm/jsonrpc-server-sdk.svg)](https://packagist.org/packages/yoanm/jsonrpc-server-sdk)
[![Packagist PHP version](https://img.shields.io/packagist/php-v/yoanm/jsonrpc-server-sdk.svg)](https://packagist.org/packages/yoanm/jsonrpc-server-sdk)

Simple server SDK to convert a json-rpc request string into json-rpc response string.

See [yoanm/symfony-jsonrpc-http-server](https://github.com/yoanm/symfony-jsonrpc-http-server) for automatic dependency injection.

See [yoanm/jsonrpc-params-symfony-validator-sdk](https://github.com/yoanm/php-jsonrpc-params-symfony-validator-sdk) for params validation.

See [yoanm/jsonrpc-server-doc-sdk](https://github.com/yoanm/php-jsonrpc-server-doc-sdk) for documentation generation.

## How to use

Sdk requires only two things : 
 - A method resolver : must implements [JsonRpcMethodResolverInterface](./src/Domain/JsonRpcMethodResolverInterface.php), resolving logic's is your own.
 - Methods : JsonRpc methods which implements [JsonRpcMethodInterface](./src/Domain/JsonRpcMethodInterface.php)
 
Sdk optionally provide :
 - Events dispatch
 - Params validation

### Simple Example
#### JSON-RPC Method
```php
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;

class DummyMethod implements JsonRpcMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(array $paramList = null)
    {
        // Handle the request
        ...
        // Then return a result
        return [
            'status' => 'done',
        ];
        // Or
        return null;
        // Or
        return 12345;
    }
}
```
#### Array method resolver (simple example)
*You can use [the one used for behat tests](./features/bootstrap/App/BehatMethodResolver.php) or this [Psr11 method resolver](https://github.com/yoanm/php-jsonrpc-server-sdk-psr11-resolver) as example*
```php
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodResolverInterface;

class ArrayMethodResolver implements JsonRpcMethodResolverInterface
{
    /** @var JsonRpcMethodInterface[] */
    private $methodList = [];

    /**
     * {@inheritdoc}
     */
    public function resolve(string $methodName) : ?JsonRpcMethodInterface
    {
        return array_key_exists($methodName, $this->methodList)
            ? $this->methodList[$methodName]
            : null
        ;
    }

    /**
     * @param JsonRpcMethodInterface $method
     * @param string                 $methodName
     */
    public function addMethod(JsonRpcMethodInterface $method, string $methodName)
    {
        $this->methodList[$methodName] = $method;
    }
}
```

Then add your method to the resolver and create the endpoint : 
```php
use Yoanm\JsonRpcServer\App\Creator\ResponseCreator;
use Yoanm\JsonRpcServer\App\Handler\ExceptionHandler;
use Yoanm\JsonRpcServer\App\Handler\JsonRpcRequestHandler;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallResponseNormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcCallSerializer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcRequestDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\JsonRpcResponseNormalizer;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;

$resolver = new ArrayMethodResolver();
$resolver->addMethod('dummy-method', new DummyMethod());

$jsonRpcSerializer = new JsonRpcCallSerializer(
    new JsonRpcCallDenormalizer(
        new JsonRpcRequestDenormalizer()
    ),
    new JsonRpcCallResponseNormalizer(
        new JsonRpcResponseNormalizer()
    )
);
$responseCreator = new ResponseCreator();
$requestHandler = new JsonRpcRequestHandler($resolver, $responseCreator);
$exceptionHandler = new ExceptionHandler($responseCreator);

$endpoint = new JsonRpcEndpoint($jsonRpcSerializer, $requestHandler, $exceptionHandler);
```

Once endpoint is ready, you can send it request string : 
```php
$requestString = <<<JSONRPC
{
    "jsonrpc": "2.0",
    "id": 1
    "method": "dummy-method"
}
JSONRPC;

$responseString = $endpoint->index($requestString);
```

`$responseString` will be the following string depending of method returned value : 
 * ```json
   {"jsonrpc":"2.0","id":1,"result":{"status":"done"}}
   ```
 * ```json
   {"jsonrpc":"2.0","id":1,"result":null}
   ```

 * ```json
   {"jsonrpc":"2.0","id":1,"result":12345}
   ```
### Events dispatch example

#### Simple event dispatcher
*You can use [the one used for behat tests](./features/bootstrap/App/BehatRequestLifecycleDispatcher.php) as example*

```php
use Yoanm\JsonRpcServer\Domain\Event\JsonRpcServerEvent;
use Yoanm\JsonRpcServer\Domain\JsonRpcServerDispatcherInterface;

/**
 * Class SimpleDispatcher
 */
class SimpleDispatcher implements JsonRpcServerDispatcherInterface
{
    /** @var callable[] */
    private $listenerList = [];

    /**
     * {@inheritdoc}
     */
    public function dispatchJsonRpcEvent(string $eventName, JsonRpcServerEvent $event = null) : void
    {
        if (!array_key_exists($eventName, $this->listenerList)) {
            return;
        }

        foreach ($this->listenerList[$eventName] as $listener) {
            $listener($event, $eventName);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addJsonRpcListener(string $eventName, $listener) : void
    {
        $this->listenerList[$eventName][] = $listener;
    }
}
```

Then bind your listeners to your dispatcher:
```php
use Yoanm\JsonRpcServer\Domain\Event\Acknowledge\OnRequestReceivedEvent;
use Yoanm\JsonRpcServer\Domain\Event\Acknowledge\OnResponseSendingEvent;
use Yoanm\JsonRpcServer\Domain\Event\Action\OnMethodSuccessEvent;

$dispatcher = new SimpleDispatcher();

$listener = function ($event, $eventName) {
    echo sprintf(
        'Received %s with event class "%s"',
        $eventName,
        get_class($event)
    );
};

$dispatcher->addJsonRpcListener(OnRequestReceivedEvent::EVENT_NAME, $listener);
$dispatcher->addJsonRpcListener(OnResponseSendingEvent::EVENT_NAME, $listener);
$dispatcher->addJsonRpcListener(OnMethodSuccessEvent::EVENT_NAME, $listener);
```

And bind dispatcher like following :
```php
$endpoint->setJsonRpcServerDispatcher($dispatcher);
$requestHandler->setJsonRpcServerDispatcher($dispatcher);
$exceptionHandler->setJsonRpcServerDispatcher($dispatcher);
```

#### Events dispatched 

##### Basic request lifecycle

 - `json_rpc_server_skd.on_request_received` / [`Acknowledge\OnRequestReceivedEvent`](./src/Domain/Event/Acknowledge/OnRequestReceivedEvent.php)
   
   Dispatched when a request has been passed to the endpoint and successfully deserialized.

   > N.B. : Lonely cases where this event is not dispatched are when the request string is not a valid JSON-RPC request. 
   > 
   > It include :
   > - Parse error exception (malformed json string)
   > - For simple request only, in case of Invalid request (not an object / missing required properties / ...). 
   >   
   >   *:warning: For batch request containing Invalid SubRequest, this event will still be dispatched*
 
 - Either
 
   - `json_rpc_server_skd.on_method_success` / [`Action\OnMethodSuccessEvent`](./src/Domain/Event/Action/OnMethodSuccessEvent.php)
   
     Dispatched **only in case JSON-RPC method has been successfully executed**.

   - `json_rpc_server_skd.on_method_failure` / [`Action\OnMethodFailureEvent`](./src/Domain/Event/Action/OnMethodFailureEvent.php)

     Dispatched **only in case JSON-RPC method throw an exception during execution**.
 
 - `json_rpc_server_skd.on_response_sending` / [`Acknowledge\OnResponseSendingEvent`](./src/Domain/Event/Acknowledge/OnResponseSendingEvent.php)
   
   Dispatched when a response has been successfully serialized by the endpoint and will be returned.

##### Additional events

###### Batch request
- `json_rpc_server_skd.on_batch_sub_request_processing` / [`Acknowledge\OnBatchSubRequestProcessingEvent`](./src/Domain/Event/Acknowledge/OnBatchSubRequestProcessingEvent.php)
   
   Dispatched before that a sub request will be processed.

 - `json_rpc_server_skd.on_batch_sub_request_processed` / [`Acknowledge\OnBatchSubRequestProcessedEvent`](./src/Domain/Event/Acknowledge/OnBatchSubRequestProcessedEvent.php)
   
   Dispatched after that a sub request has been processed (regardless of the success or failure of the sub request method execution).
   
###### Exception
`json_rpc_server_skd.on_exception` / [`Action\OnExceptionEvent`](./src/Domain/Event/Action/OnExceptionEvent.php)
   
Dispatched when an exception occurred during sdk execution

##### Action vs Acknowledge events

###### Acknowledge
They have only an acknowledge purpose.

They are grouped under `Yoanm\JsonRpcServer\Domain\Event\Acknowledge` namespace.

###### Action
They allow you to override stuffs.

They are grouped under `Yoanm\JsonRpcServer\Domain\Event\Action` namespace.

Here, the list : 
 - [`Action\OnMethodSuccessEvent`](./src/Domain/Event/Action/OnMethodSuccessEvent.php) allow you to update/change the result of the method.
 - [`Action\OnMethodFailureEvent`](./src/Domain/Event/Action/OnMethodFailureEvent.php) allow you to update/change the exception thrown by the method.
 - [`Action\OnExceptionEvent`](./src/Domain/Event/Action/OnExceptionEvent.php) allow you to update/change the exception thrown.

### Params validation example

*You can use this [JSON-RPC params symfony validator](https://github.com/yoanm/php-jsonrpc-params-symfony-validator-sdk) as example*

To validate params for a given method, do the following :
```php
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\JsonRpcMethodParamsValidatorInterface;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcRequest;

$validator = new class implements JsonRpcMethodParamsValidatorInterface
{
    public function validate(JsonRpcRequest $jsonRpcRequest, JsonRpcMethodInterface $method) : array
    {
        if (!(/** Skip unexpected method */)) {
            return [];
        }

        // Create your violations based on what you want
        $paramList = $jsonRpcRequest->getParamList();
        $violation = "???";

        return [$violation];
    }
};

$requestHandler->setMethodParamsValidator($validator);
```

## Makefile
```bash
# Install and configure project
make build
# Launch tests (PHPUnit & behat)
make test
# Check project code style
make codestyle
# Generate PHPUnit coverage
make coverage
# Generate Behat coverage
make behat-coverage
```

## Contributing
See [contributing note](./CONTRIBUTING.md)
