# PHP JSON-RPC server sdk
 [![License](https://img.shields.io/github/license/yoanm/php-jsonrpc-server-sdk.svg)](https://github.com/yoanm/php-jsonrpc-server-sdk) [![Code size](https://img.shields.io/github/languages/code-size/yoanm/php-jsonrpc-server-sdk.svg)](https://github.com/yoanm/php-jsonrpc-server-sdk) [![PHP Versions](https://img.shields.io/badge/php-7.0%20%2F%207.1%20%2F%207.2-8892BF.svg)](https://php.net/)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yoanm/php-jsonrpc-server-sdk/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yoanm/php-jsonrpc-server-sdk/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/yoanm/php-jsonrpc-server-sdk/badges/build.png?b=master)](https://scrutinizer-ci.com/g/yoanm/php-jsonrpc-server-sdk/build-status/master) [![Code Coverage](https://scrutinizer-ci.com/g/yoanm/php-jsonrpc-server-sdk/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yoanm/php-jsonrpc-server-sdk/?branch=master)

[![Travis Build Status](https://img.shields.io/travis/yoanm/php-jsonrpc-server-sdk/master.svg?label=travis)](https://travis-ci.org/yoanm/php-jsonrpc-server-sdk) [![Travis PHP versions](https://img.shields.io/travis/php-v/yoanm/php-jsonrpc-server-sdk.svg)](https://travis-ci.org/yoanm/php-jsonrpc-server-sdk)

[![Latest Stable Version](https://img.shields.io/packagist/v/yoanm/jsonrpc-server-sdk.svg)](https://packagist.org/packages/yoanm/jsonrpc-server-sdk) [![Packagist PHP version](https://img.shields.io/packagist/php-v/yoanm/jsonrpc-server-sdk.svg)](https://packagist.org/packages/yoanm/jsonrpc-server-sdk)

Simple server SDK to convert a json-rpc request string into json-rpc response string

## How to use

Sdk requires only two things : 
 - A method resolver : must implement [MethodResolverInterface](./src/Domain/Model/MethodResolverInterface.php), resolving logic's is your own.
 - Methods : JsonRpc methods that implement [JsonRpcMethodInterface](./src/Domain/Model/JsonRpcMethodInterface.php)
 
Sdk optionally provide :
 - Events dispatch
 - Params validation (thanks to event dispatching)
 
:warning: For dependency injection see [JSON-RPC server symfony bundle](https://github.com/yoanm/symfony-jsonrpc-http-server)

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

class ArrayMethodResolver implements MethodResolverInterface
{
    /** @var JsonRpcMethodInterface[] */
    private $methodList = [];

    /**
     * {@inheritdoc}
     */
    public function resolve(string $methodName)
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
$resolver->addMethod(
    'dummy-method'
    new DummyMethod()
);

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
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;

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
    public function dispatchJsonRpcEvent(string $eventName, JsonRpcServerEvent $event = null)
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
    public function addJsonRpcListener(string $eventName, $listener)
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

### Params validation example
**Params validation is based on event dispatching and so, requires dispatcher configuration like described in previous section**

*You can use this [JSON-RPC params symfony validator](https://github.com/yoanm/php-jsonrpc-params-symfony-validator-sdk) as example*

To validate params for a given method, do the following :
```php
use Yoanm\JsonRpcServer\Domain\Event\Action\ValidateParamsEvent;

$validator = function (ValidateParamsEvent $event) {
    $method = $event->getMethod();
    $paramList = $event->getParamList();
    if (/** Select the right method */) {
        // Create your violations based on what you want
        $violation = "???";
        // Then add it/them to the event
        $event->addViolation($violation);
    }
};

$dispatcher->addJsonRpcListener(ValidateParamsEvent::EVENT_NAME, $validator);
```

## Contributing
See [contributing note](./CONTRIBUTING.md)
