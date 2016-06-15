<?php

namespace AlanKent\Alexa\App;

/**
 * Definition of an application to process Alexa requests. The rest of the module translates
 * JSON HTTP POST requests from Amazon into these function calls.
 */
interface AlexaApplicationInterface
{
    /**
     * The user has launched a new session, but not provided an intent at this stage.
     * For example, the user has said something like "Alexa, connect to My Magento Store".
     * @param SessionDataInterface $sessionData Data structure for holding session data that is
     * persisted between requests. This data structure is deleted when the user's session
     * expires. Can be used to hold session state (such as current item in a list).
     * @param CustomerDataInterface $customerData Data structure for holding customer data this is
     * persisted between requests.
     * @return ResponseData The response to return to the Alex (to be vocalized).
     */
    public function launchRequest(SessionDataInterface $sessionData,
                                  CustomerDataInterface $customerData);

    /**
     * Process the specified intent (request) from a user. This is the heart of request
     * processing.
     * @param SessionDataInterface $sessionData Data structure for holding session data that is
     * persisted between requests. This data structure is deleted when the user's session
     * expires.
     * @param CustomerDataInterface $customerData Data structure for holding customer data this is
     * persisted between requests.
     * @param string $intentName The name of the Alex intent, from the Alex configuration files.
     * The intent is the symbolic identifier associated with a command. There may be multiple
     * phrases that map to the same intent.
     * @param array $slots An associative array of slot name/value pairs. Note that Alexa
     * may still send a request with omitted slot data if it was not specified in the request,
     * so the caller should check whether expected slot values have been provided or not.
     * @return ResponseData The response to return to the Alex (to be vocalized).
     */
    public function intentRequest(SessionDataInterface $sessionData,
                                  CustomerDataInterface $customerData,
                                  $intentName,
                                  $slots);

    /**
     * Alexa has requested the specified session to be ended, for example because the user
     * has not responded within the timeout grace period.
     * @param SessionDataInterface $sessionData The current session. This will automatically be
     * marked as 'should end session' after the current request.
     * @param CustomerDataInterface $customerData The current user information.
     * @param string $reason From the Alexa documentation:
     * "USER_INITIATED": The user explicitly ended the session.
     * "ERROR": An error occurred that caused the session to end.
     * "EXCEEDED_MAX_REPROMPTS": The user either did not respond or responded with an utterance
     * that did not match any of the intents defined in your voice interface.
     * @return ResponseData The response to return to the Alex (to be vocalized).
     */
    public function endSession(SessionDataInterface $sessionData,
                               CustomerDataInterface $customerData,
                               $reason);
}