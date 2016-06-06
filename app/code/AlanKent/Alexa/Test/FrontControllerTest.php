<?php

namespace AlanKent\Alexa\Test;


use AlanKent\Alexa\App\CustomerDataFactory;
use AlanKent\Alexa\App\FrontController;
use AlanKent\Alexa\App\SessionDataFactory;
use AlanKent\Alexa\App\StateManagement;
use AlanKent\Alexa\App\ResponseData;

/**
 * Unit test for Alexa front controller. This is the heart of the
 * processing engine - it parses inbound requests, invokes the
 * applicaiton method, formulates the JSON response, etc.
 */
class FrontControllerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Controller\ResultFactory */
    private $resultFactory;

    /** @var \Magento\Framework\App\AreaList */
    private $areaList;

    /** @var \Magento\Framework\Config\ScopeInterface */
    private $configScope;

    /** @var \AlanKent\Alexa\App\SessionDataFactoryInterface */
    private $sessionDataFactory;

    /** @var \AlanKent\Alexa\App\CustomerDataFactoryInterface */
    private $customerDataFactory;

    /** @var \AlanKent\Alexa\App\StateManagementInterface */
    private $stateManagement;

    /** @var \AlanKent\Alexa\App\AlexaApplicationInterface */
    private $alexaApp;

    /** @var \AlanKent\Alexa\App\FrontController */
    private $frontController;

    /** @var \Magento\Framework\Controller\Result\Raw */
    private $rawContent;

    /** @var \Magento\Framework\Controller\Result\Json */
    private $jsonContent;

    /**
     * Set up common infrastructure used by all the tests.
     */
    public function setUp()
    {
        // Need get methods so can access contents and response code from unit test.
        $this->rawContent = new class extends \Magento\Framework\Controller\Result\Raw {
            public function getContents() { return $this->contents; }
            public function getHttpResponseCode() { return $this->httpResponseCode; }
        };

        // Need get methods so can access contents and response code from unit test.
        $this->jsonContent = new class extends \Magento\Framework\Controller\Result\Json {
            public function __construct() { }
            public function getContents() { return $this->json; }
            public function getHttpResponseCode() { return $this->httpResponseCode; }
        };

        // 'Create' methods for raw and json should return the above two objects
        // so we can access the result later.
        $this->resultFactory = $this->getMockBuilder('\Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory
            ->expects($this->any())
            ->method('create')
            ->will(
                $this->returnValueMap(
                    array(
                        array('raw', [], $this->rawContent),
                        array('json', [], $this->jsonContent)
                    )
                )
            );

        // Always return the 'alexa' area.
        $this->areaList = $this->getMockBuilder('\Magento\Framework\App\AreaList')
            ->disableOriginalConstructor()
            ->getMock();
        $this->areaList->expects($this->any())
            ->method('getFrontName')
            ->will($this->returnValue('alexa'));

        // Always return the 'alexa' area.
        $this->configScope = $this->getMockBuilder('\Magento\Framework\Config\ScopeInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configScope->expects($this->any())
            ->method('getConfigScope')
            ->will($this->returnValue('alexa'));

        // Use the real data structures - they just hold simple data structures.
        // This way we can check the values in the test methods.
        $this->sessionDataFactory = new SessionDataFactory();
        $this->customerDataFactory = new CustomerDataFactory();
        $this->stateManagement = new StateManagement($this->sessionDataFactory, $this->customerDataFactory);

        // Test cases can mock this out more fully if required.
        $this->alexaApp = $this->getMock('\AlanKent\Alexa\App\AlexaApplicationInterface');

        // Build a front controller, pointing to all of the above.
        $this->frontController = new FrontController($this->resultFactory, $this->areaList, $this->configScope, $this->alexaApp, $this->stateManagement);
    }

    /**
     * Test the basics - can we construct the front controller?
     */
    public function testConstructor()
    {
        $this->assertNotNull($this->frontController);
    }

    /**
     * Make sure an invalid URL is rejected.
     */
    public function testBadUrl()
    {
        // Return a POST to /alexa/blah (not the correct path of /alexa/v1.0).
        $request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->any())->method('getPathInfo')->will($this->returnValue('/alexa/blah'));
        $request->expects($this->any())->method('isPost')->will($this->returnValue(true));

        $this->frontController->dispatch($request);
        
        $this->assertEquals(404, $this->getHttpResponseCode());
        $this->assertContains('Unsupported URL', $this->getContents());
    }

    /**
     * Make sure wrong method is rejected.
     */
    public function testNotPost()
    {
        // Return a GET of /alexa/v1.0 (not a POST). Should fail.
        $request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->any())->method('getPathInfo')->will($this->returnValue('/alexa/v1.0'));
        $request->expects($this->any())->method('isPost')->will($this->returnValue(false));

        $this->frontController->dispatch($request);

        $this->assertEquals(500, $this->getHttpResponseCode());
        $this->assertContains('Only POST', $this->getContents());
    }

    /**
     * PUT to good URL, but with minimal (malformed) JSON content.
     */
    public function testEmptyJson()
    {
        $request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->any())->method('getPathInfo')->will($this->returnValue('/alexa/v1.0'));
        $request->expects($this->any())->method('isPost')->will($this->returnValue(true));
        $request->expects($this->any())->method('getContent')->will($this->returnValue('{}'));

        $this->frontController->dispatch($request);

        $this->assertEquals(500, $this->getHttpResponseCode());
        $this->assertContains('Malformed', $this->getContents());
    }

    /**
     * Test a valid launch request.
     */
    public function testLaunch()
    {
        $request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->any())->method('getPathInfo')->will($this->returnValue('/alexa/v1.0'));
        $request->expects($this->any())->method('isPost')->will($this->returnValue(true));
        $request->expects($this->any())->method('getContent')->will($this->returnValue(<<<EOF
{
  "version": "1.0",
  "session": {
    "new": true,
    "sessionId": "S1",
    "application": {
      "applicationId": "MyApp"
    },
    "attributes": {
      "myAttribute": "blah"
    },
    "user": {
      "userId": "Alan Kent",
      "accessToken": "1234567890"
    }
  },
  "request":{
    "type": "LaunchRequest",
    "requestId": "1234",
    "timestamp": "2015-05-13T12:34:56Z"
  }
}
EOF
));

        $launchResponse = new ResponseData();
        $launchResponse->setShouldEndSession(false);
        $launchResponse->setResponseText("Response text");
        $launchResponse->setCardSimple("Simple Title", "Simple Content");
        $this->alexaApp
            ->expects($this->any())
            ->method('launchRequest')
            ->willReturn($launchResponse);

        $this->frontController->dispatch($request);

        $this->assertEquals(200, $this->getHttpResponseCode());
        $this->assertContains('version', $this->getContents());
        $this->assertContains('"shouldEndSession":false', $this->getContents());
        $this->assertContains('"card"', $this->getContents());
        $this->assertContains('"type":"Simple"', $this->getContents());
        $this->assertContains('"title":"Simple Title"', $this->getContents());
        $this->assertContains('"content":"Simple Content"', $this->getContents());
    }

    /**
     * Helper function to get HTTP response code from either the JSON or raw output, depending
     * on what form of output was generated.
     */
    private function getHttpResponseCode()
    {
        if ($this->jsonContent->getHttpResponseCode() === null) {
            return $this->rawContent->getHttpResponseCode();
        } else {
            return $this->jsonContent->getHttpResponseCode();
        }
    }

    /**
     * Helper function to get HTTP response payload from either the JSON or raw output, depending
     * on what form of output was generated.
     */
    private function getContents()
    {
        if ($this->jsonContent->getHttpResponseCode() === null) {
            return $this->rawContent->getContents();
        } else {
            return $this->jsonContent->getContents();
        }
    }
}
