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
 
:warning: No dependency injection is managed in this library 

### Example
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
*You could take example on [the one used for behat tests](./features/bootstrap/App/BehatMethodResolver.php) or on this [Psr11 method resolver](https://github.com/yoanm/php-jsonrpc-server-sdk-psr11-resolver)*
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
    "method": "dummy-method",
    "params": {
        "my-required-key": "a-value"
    }
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

## Contributing
See [contributing note](./CONTRIBUTING.md)
