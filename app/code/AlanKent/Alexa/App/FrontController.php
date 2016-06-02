<?php
namespace AlanKent\Alexa\App;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\AreaList;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\HTTP\PhpEnvironment\Request as HttpRequest;

/**
 * Front controller for the 'alexa' area. Converts web service requests
 * (HTTP POST of JSON encoded data) into appropriate PHP function calls.
 */
class FrontController implements FrontControllerInterface
{
    /** @var ResultFactory */
    private $resultFactory;

    /** @var string */
    private $areaFrontName;

    /** @var AlexaApplicationInterface */
    private $handler;

    /** @var StateManagementInterface */
    private $stateManagement;

    /**
     * FrontController constructor.
     * @param ResultFactory $resultFactory
     * @param AreaList $areaList
     * @param ScopeInterface $configScope
     * @param AlexaApplicationInterface $handler
     * @param StateManagementInterface $stateManagement
     */
    public function __construct(
        ResultFactory $resultFactory,
        AreaList $areaList,
        ScopeInterface $configScope,
        AlexaApplicationInterface $handler,
        StateManagementInterface $stateManagement
    ) {
        $this->resultFactory = $resultFactory;
        $this->areaFrontName = $areaList->getFrontName($configScope->getCurrentScope());
        $this->handler = $handler;
        $this->stateManagement = $stateManagement;
    }

    /**
     * @param RequestInterface $request
     * @return ResultInterface
     */
    public function dispatch(RequestInterface $request)
    {
        // RequestInterface does not have all the methods yet. This gives better type hinting.
        /** @var HttpRequest $req */
        $req = $request;

        // Support multiple versions of the Alexa protocol.
        $v1ProtocolPath = '/' . $this->areaFrontName . '/v1.0';

        try {

            // See if /alexa/v{version} (reject other URLs for now).
            if ($req->getPathInfo() !== $v1ProtocolPath) {
                throw new \Exception("Unsupported URL path: instead of {$req->getPathInfo()} use $v1ProtocolPath.", 404);
            }

            if (!$req->isPost()) {
                throw new \Exception("Only POST requests containing JSON data are supported.");
            }

            // Decode JSON request.
            $alexaRequest = json_decode($req->getContent(), true);

            // Process the request.
            $alexaResponse = $this->processV1AlexaRequest($alexaRequest);

            // Serialize the result.
            /** @var \Magento\Framework\Controller\Result\Json $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $result->setData($alexaResponse);

            return $result;

        } catch (\Exception $e) {

            /** @var \Magento\Framework\Controller\Result\Raw $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
            $result->setContents($e->getMessage());
            $result->setHttpResponseCode($e->getCode() == 0 ? 500 : $e->getCode());
            return $result;
        }
    }

    /**
     * Process a request according to the Alexa protocol.
     * @param array $alexaRequest Associatiate array that is the parsed JSON request.
     * @return array Associative array to encode as JSON.
     * @throws \Exception Thrown if fail to parse message.
     */
    private function processV1AlexaRequest($alexaRequest) {

        // https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/alexa-skills-kit-interface-reference

        if ($alexaRequest['version'] != '1.0') {
            throw new \Exception("Only version '1.0' is supported, not {$alexaRequest['version']}.");
        }

        // Process session data.
        $session = $alexaRequest['session'];
        $newSession = $session['new'];
        $sessionId = $session['sessionId'];
        $userId = $session['user']['userId'];
        $accessToken = isset($session['user']['accessToken']) ? $session['user']['accessToken'] : null;

        // Requests have different formats, but always have these fields.
        $request = $alexaRequest['request'];
        $requestType = $request['type'];
        $requestId = $request['requestId'];
        $timestamp = $request['timestamp'];

        /** @var SessionDataInterface $sessionData */
        if ($newSession) {
            $sessionData = $this->stateManagement->createNewSession($sessionId);
        } else {
            $sessionData = $this->stateManagement->retrieveSessionData($sessionId);
            $this->checkIfReplayAttack($sessionData->getLastTimestamp(), $timestamp);
        }
        if (isset($session['attributes'])) {
            $sessionData->setSessionAttributes($session['attributes']);
        }

        /** @var CustomerDataInterface $customerData */
        $customerData = $this->stateManagement->retrieveCustomerData($userId, $accessToken);

        // Process the request.
        switch ($requestType) {
            case "LaunchRequest" : {
                $responseData = $this->handler->launchRequest($sessionData, $customerData);
                break;
            }
            case "IntentRequest" : {
                $intentName = $request['intent'];
                $slots = array();
                foreach ($request['intent']['slots'] as $nameAndValue) {
                    $slots[$nameAndValue['name']] = $nameAndValue['value'];
                }
                $responseData = $this->handler->intentRequest($sessionData, $customerData, $intentName, $slots);
                break;
            }
            case "SessionEndedRequest" : {
                $reason = $request['reason'];
                $responseData = $this->handler->endSession($sessionData, $customerData, $reason);
                $sessionData->setShouldEndSession(true);
                break;
            }
            default: {
                throw new \Exception("Unknown Alexa request type '{$request['type']}'.");
            }
        }

        $alexaResponse = array();
        $alexaResponse['version'] = "1.0";
        $attributes = $sessionData->getSessionAttributes();
        if ($attributes != null && !empty($attributes)) {
            $alexaResponse['sessionAttributes'] = $attributes;
        }
        $response = array();
        $alexaResponse['response'] = $response;
        $response['shouldEndSession'] = $sessionData->getShouldEndSession();
        //$response['outputSpeech'] = TODO
        //$response['card'] = TODO
        //$response['reprompt']['outputSpeech'] = TODO

        // Save away any data we wish to preserve in a Magento table.
        $this->stateManagement->saveSessionUpdates($sessionData, $timestamp);
        $this->stateManagement->saveCustomerUpdates($customerData);

        return $alexaResponse;
    }
    
    /**
     * Throw exception if things look like a replay attach is going on.
     */
    private function checkIfReplayAttack($lastRequestTimestamp, $currentRequestTimestamp) {
        if ($lastRequestTimestamp === null || $currentRequestTimestamp === null) {
            // We don't have sufficient data to detect a replay attack.
            return;
        }
        if ($currentRequestTimestamp < $lastRequestTimestamp) {
            throw new \Exception("New request timestamp '$currentRequestTimestamp'' is earlier than previous request timestamp '$lastRequestTimestamp''.");
        }
    }
}
