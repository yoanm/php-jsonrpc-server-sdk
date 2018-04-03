<?php
namespace Tests\Functional\BehatContext;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Tests\Functional\BehatContext\App\BehatMethodResolver;
use Tests\Functional\BehatContext\App\FakeEndpointCreator;
use Yoanm\JsonRpcServer\Domain\Model\MethodResolverInterface;
use Yoanm\JsonRpcServer\Infra\Endpoint\JsonRpcEndpoint;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    const KEY_JSON_RPC = 'jsonrpc';
    const KEY_ID = 'id';
    const KEY_RESULT = 'result';
    const KEY_ERROR = 'error';

    const SUB_KEY_ERROR_CODE = 'code';
    const SUB_KEY_ERROR_MESSAGE = 'message';
    const SUB_KEY_ERROR_DATA = 'data';

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
    public function thenLastResponseShouldBeAValidJsonRpcResult()
    {
        $this->validateJsonRpcSuccessReponse($this->getLastResponseDecoded());
    }

    /**
     * @Then last response should be a valid json-rpc error
     */
    public function thenLastResponseShouldBeAValidJsonRpcError()
    {
        $this->validateJsonRpcErrorReponse($this->getLastResponseDecoded());
    }

    /**
     * @Then last response should be a valid json-rpc batch response
     */
    public function thenLastResponseShouldBeAValidJsonRpcBatchResponse()
    {
        // Decode content to get rid of any indentation/spacing/... issues
        $decoded = $this->getLastResponseDecoded();
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
            $this->jsonDecode($expectedResult->getRaw()),
            $this->getLastResponseDecoded()
        );
    }

    /**
     * @Then I should have an empty response
     */
    public function thenIShouldHaveAnEmptyResponse()
    {
        // Decode content to get rid of any indentation/spacing/... issues
        Assert::assertEmpty($this->getLastResponseDecoded());
    }

    /**
     * @Then response should contain the following:
     */
    public function thenResponseShouldContainTheFollowing(PyStringNode $expectedResult)
    {
        // Decode content to get rid of any indentation/spacing/... issues
        Assert::assertArraySubset(
            $this->jsonDecode($expectedResult->getRaw()),
            $this->getLastResponseDecoded()
        );
    }

    private function validateJsonRpcErrorReponse($decoded)
    {
        Assert::assertTrue(is_array($decoded), 'An error response must be an array');
        // Validate required keys
        Assert::assertArrayHasKey(
            self::KEY_JSON_RPC,
            $decoded,
            'An error response must have a "'.self::KEY_JSON_RPC.'" key'
        );
        Assert::assertArrayHasKey(
            self::KEY_ID,
            $decoded,
            'An error response must have an "'.self::KEY_ID.'" key'
        );
        Assert::assertArrayHasKey(
            self::KEY_ERROR,
            $decoded,
            'An error response must have an "'.self::KEY_ERROR.'" key'
        );
        // Validate error required keys
        Assert::assertArrayHasKey(
            self::SUB_KEY_ERROR_CODE,
            $decoded[self::KEY_ERROR],
            'An error response must have a "'.self::SUB_KEY_ERROR_CODE.'" key under "'.self::KEY_ERROR.'"'
        );
        Assert::assertTrue(
            is_int($decoded[self::KEY_ERROR][self::SUB_KEY_ERROR_CODE]),
            'Error code must be an integer'
        );
        Assert::assertTrue(
            $decoded[self::KEY_ERROR][self::SUB_KEY_ERROR_CODE] >= -32768
            && $decoded[self::KEY_ERROR][self::SUB_KEY_ERROR_CODE] <= -32000,
            'Error code must be between -32768 and -32000'
        );
        Assert::assertArrayHasKey(
            self::SUB_KEY_ERROR_MESSAGE,
            $decoded[self::KEY_ERROR],
            'An error response must have an "'.self::SUB_KEY_ERROR_MESSAGE.'" key under "'.self::KEY_ERROR.'"'
        );
        // Validate nothing else exist in the response
        Assert::assertCount(
            3,
            $decoded,
            'An error response must not contains any other keys than "'
            .self::KEY_JSON_RPC.'", "'.self::KEY_ID.'" and "'.self::KEY_ERROR.'"'
        );
    }

    private function validateJsonRpcSuccessReponse($decoded)
    {
        Assert::assertTrue(is_array($decoded), 'A response must be an array');
        Assert::assertArrayHasKey(self::KEY_JSON_RPC, $decoded, 'A response must have a "'.self::KEY_JSON_RPC.'" key');
        Assert::assertArrayHasKey(self::KEY_ID, $decoded, 'A response must have an "'.self::KEY_ID.'" key');
        Assert::assertFalse(is_null($decoded[self::KEY_ID]), 'A response id must not be null');
        Assert::assertArrayHasKey(self::KEY_RESULT, $decoded, 'A response must have a "'.self::KEY_RESULT.'" key');
        Assert::assertArrayHasKey(
            array_key_exists(self::KEY_ERROR, $decoded),
            'A response must not have an "'.self::KEY_ERROR.'" key'
        );
    }

    private function getLastResponseDecoded()
    {
        return $this->jsonDecode($this->lastResponse);
    }

    private function jsonDecode($content)
    {
        $result = json_decode($content, true);
        $error = json_last_error();
        if ($error !== JSON_ERROR_NONE) {
            $errorMessage = json_last_error_msg();
            throw new \Exception("Json parse error ${error} => ${errorMessage} : ${content}");
        }

        return $result;
    }
}
