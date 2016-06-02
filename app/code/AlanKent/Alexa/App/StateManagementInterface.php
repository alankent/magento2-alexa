<?php
/**
 * Created by PhpStorm.
 * User: akent
 * Date: 6/1/2016
 * Time: 11:14 PM
 */

namespace AlanKent\Alexa\App;

/**
 * Manages Magento state information, store in a local databas table.
 * Note that preserving state is optional between requests. The default behavior is to
 * not persist state in a database table.
 */
interface StateManagementInterface
{
    /**
     * Create a new session with the specified session id.
     * @param string $sessionId The session id to associate with the newly created session.
     * @return SessionDataInterface Data structure for holding session information.
     */
    public function createNewSession($sessionId);

    /**
     * Return session data for existing session with specified session id.
     * Throws an \Exception is the session does not exist.
     * @param string $sessionId Session identifer.
     * @param string $timestamp ISO format timestamp.
     * @return SessionDataInterface Data structure holding session information.
     */
    public function retrieveSessionData($sessionId);

    /**
     * Retrieve data structure for holding information about the specified customer.
     * It is up to the implementation as to whether an access token must be supplied
     * or not. Normally this method is assumed to always succeed.
     * @param string $userId The ID of the user.
     * @param string $accessToken If the user is authenticated, this token holds authentication details.
     * @return CustomerDataInterface Data structure for holding information about the customer.
     */
    public function retrieveCustomerData($userId, $accessToken);

    /**
     * Persist any session data that should be preserved until the next request arrives.
     * If $sessionData->getShouldEndSession() returns true, any persistent session data should
     * be deleted.
     * @param SessionDataInterface $sessionData The session data to be saved.
     * @param string $timestamp The timestamp of the current request. This timestamp must
     * be returned upon the next request as a part of detecting replay attacks.
     */
    public function saveSessionUpdates($sessionData, $timestamp);

    /**
     * Save any information about the customer that should persist til the next Alex request
     * arrives.
     * @param CustomerDataInterface $customerData The data to be saved.
     */
    public function saveCustomerUpdates($customerData);
}