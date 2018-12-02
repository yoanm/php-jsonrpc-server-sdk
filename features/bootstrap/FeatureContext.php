<?php
namespace Tests\Functional\BehatContext;

use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit\Framework\Assert;
use Tests\Functional\BehatContext\Helper\FakeEndpointCreator;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends AbstractContext
{
    const KEY_JSON_RPC = 'jsonrpc';
    const KEY_ID = 'id';
    const KEY_RESULT = 'result';
    const KEY_ERROR = 'error';

    const SUB_KEY_ERROR_CODE = 'code';
    const SUB_KEY_ERROR_MESSAGE = 'message';
    const SUB_KEY_ERROR_DATA = 'data';

    /** @var string|null */
    private $lastResponse = null;
    /** @var  EventsContext */
    private $eventsContext;

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function beforeScenario(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        if ($environment instanceof InitializedContextEnvironment) {
            $this->eventsContext = $environment->getContext(EventsContext::class);
        }
    }

    /**
     * @When I send following payload:
     */
    public function whenISendTheFollowingPayload(PyStringNode $payload)
    {
        $endpoint = (new FakeEndpointCreator())->create($this->eventsContext->getDispatcher());

        $this->lastResponse = $endpoint->index($payload->getRaw());
    }

    /**
     * @Then last response should be a valid json-rpc error
     */
    public function thenLastResponseShouldBeAValidJsonRpcError()
    {
        $this->validateJsonRpcErrorReponse($this->getLastResponseDecoded());
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

    private function getLastResponseDecoded()
    {
        return $this->jsonDecode($this->lastResponse);
    }
}
