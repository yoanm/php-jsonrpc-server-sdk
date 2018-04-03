<?php
namespace Tests\Functional\BehatContext;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Prophecy\Prophet;
use Tests\Functional\BehatContext\App\BehatMethodResolver;
use Tests\Functional\BehatContext\App\FakeEndpointCreator;
use Yoanm\JsonRpcServer\App\Creator\CustomExceptionCreator;
use Yoanm\JsonRpcServer\App\Manager\MethodManager;
use Yoanm\JsonRpcServer\App\RequestHandler;
use Yoanm\JsonRpcServer\App\Serialization\RequestDenormalizer;
use Yoanm\JsonRpcServer\App\Serialization\ResponseNormalizer;
use Yoanm\JsonRpcServer\Domain\Exception\JsonRpcException;
use Yoanm\JsonRpcServer\Domain\Model\JsonRpcMethodInterface;
use Yoanm\JsonRpcServer\Domain\Model\MethodResolverInterface;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;
use Yoanm\JsonRpcServer\Infra\Serialization\RawRequestSerializer;
use Yoanm\JsonRpcServer\Infra\Serialization\RawResponseSerializer;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /** @var JsonRpcEndpoint */
    private $endpoint;
    /** @var MethodResolverInterface */
    private $methodResolver;
    /** @var string */
    private $lastResponse;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->methodResolver = new BehatMethodResolver();

        $this->endpoint = (new FakeEndpointCreator())->create($this->methodResolver);
    }

    /**
     * @When I send following payload:
     */
    public function whenISendTheFollowingPayload(PyStringNode $payload)
    {
        $this->lastResponse = $this->endpoint->index($payload->getRaw());
    }

    /**
     * @Then last response should be a valid json-rpc result
     */
    public function thenLastResponseShouldHaveAResult()
    {
        $this->validateJsonRpcSuccessReponse(json_decode($this->lastResponse, true));
    }

    /**
     * @Then last response should be a valid json-rpc error
     */
    public function thenLastResponseShouldHaveAnError()
    {
        $this->validateJsonRpcErrorReponse(json_decode($this->lastResponse, true));
    }

    /**
     * @Then last response should be a valid json-rpc batch response
     */
    public function thenLastResponseShouldBeAValidJsonRpcBatchResponse()
    {
        // Decode content to get rid of any indentation/spacing/... issues
        $decoded = json_decode($this->lastResponse, true);
        Assert::assertTrue(is_array($decoded), 'A batch response must be an array');
        Assert::assertTrue(count($decoded) > 0, 'A batch response must contains at least one sub response');
    }

    /**
     * @Then I should have the following response:
     */
    public function thenIShouldHaveTheFollowingResponse(PyStringNode $expectedResult)
    {
        // Decode content to get rid of any indentation/spacing/... issues
        Assert::assertEquals(
            json_decode($expectedResult->getRaw(), true),
            json_decode($this->lastResponse, true)
        );
    }

    /**
     * @Then I should have an empty response
     */
    public function thenIShouldHaveAnEmptyResponse()
    {
        // Decode content to get rid of any indentation/spacing/... issues
        Assert::assertEmpty(json_decode($this->lastResponse, true));
    }

    /**
     * @Then response should contain the following:
     */
    public function thenResponseShouldContainTheFollowing(PyStringNode $expectedResult)
    {
        // Decode content to get rid of any indentation/spacing/... issues
        Assert::assertArraySubset(
            json_decode($expectedResult->getRaw(), true),
            json_decode($this->lastResponse, true)
        );
    }

    private function validateJsonRpcErrorReponse($decoded)
    {
        Assert::assertTrue(is_array($decoded), 'An error response must be an array');
        // Validate required keys
        Assert::assertArrayHasKey('jsonrpc', $decoded, 'An error response must have a "jsonrpc" key');
        Assert::assertArrayHasKey('id', $decoded, 'An error response must have an "id" key');
        Assert::assertArrayHasKey('error', $decoded, 'An error response must have an "error" key');
        // Validate error required keys
        Assert::assertArrayHasKey(
            'code',
            $decoded['error'],
            'An error response must have a "code" key under "error"'
        );
        Assert::assertTrue(
            is_int($decoded['error']['code']),
            'Error code must an integer'
        );
        Assert::assertTrue(
            $decoded['error']['code'] >= -32768 && $decoded['error']['code'] <= -32000,
            'Error code must be between -32768 and -32000'
        );
        Assert::assertArrayHasKey(
            'message',
            $decoded['error'],
            'An error response must have an "message" key under "error"'
        );
        // Validate nothing else exist in the response
        Assert::assertCount(
            3,
            $decoded,
            'An error response must not contains any other keys than "jsonrpc", "id" and "error"'
        );

    }

    private function validateJsonRpcSuccessReponse($decoded)
    {
        Assert::assertTrue(is_array($decoded), 'A response must be an array');
        Assert::assertArrayHasKey('jsonrpc', $decoded, 'A response must have a "jsonrpc" key');
        Assert::assertArrayHasKey('id', $decoded, 'A response must have an "id" key');
        Assert::assertFalse(is_null($decoded['id']), 'A response id must not be null');
        Assert::assertArrayHasKey('result', $decoded, 'A response must have a "result" key');
        Assert::assertFalse(array_key_exists('error', $decoded), 'A response must not have an "error" key');
    }
}
