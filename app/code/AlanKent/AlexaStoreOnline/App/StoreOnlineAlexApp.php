<?php

namespace AlanKent\AlexaStoreOnline\App;

use AlanKent\Alexa\App\AlexaApplicationInterface;
use AlanKent\Alexa\App\CustomerDataInterface;
use AlanKent\Alexa\App\ResponseData;
use AlanKent\Alexa\App\SessionDataInterface;

/**
 * Application to check if store is online.
 */
class StoreOnlineAlexApp implements AlexaApplicationInterface
{
    /**
     * @inheritdoc
     */
    public function launchRequest($sessionData, $customerData)
    {
        $response = new ResponseData();
        $response->setShouldEndSession(true);
        return $response;
    }

    /**
     * @inheritdoc
     */
    public function intentRequest($sessionData, $customerData, $intentName, $slots)
    {
        $response = new ResponseData();
        $response->setResponseText("Your store is online. At least I think it is online!");
        $response->setCardSimple("Store Status", "Your store is probably online.");
        $response->setShouldEndSession(true);
        return $response;
    }

    /**
     * @inheritdoc
     */
    public function endSession($sessionData, $customerData, $reason)
    {
        $response = new ResponseData();
        $response->setShouldEndSession(true);
        return $response;
    }
}