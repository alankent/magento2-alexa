<?php
/**
 * Created by PhpStorm.
 * User: akent
 * Date: 6/1/2016
 * Time: 10:30 PM
 */

namespace AlanKent\Alexa\App;


/**
 * Session data holds information about the current session. An Alexa request may be a single
 * request/response pair with the session immediately ended, or the application may wish to
 * ask the user for more information, in which case the session is preserved.
 */
interface SessionDataInterface
{
    /**
     * Session id for this session.
     * @return string The session ID is always available.
     */
    public function getSessionId();

    /**
     * Timestamp of previous request in this session, or null if new session or previous
     * timestamp is unknown.
     * @return string|null Timestamp of the previous request for this session ISO data format,
     * or null if there was no previous request or the timestamp was not persisted.
     */
    public function getLastTimestamp();

    /**
     * Set to true to tell Alexa to end the current session. The application may wish to keep
     * the current session going if it wants more information from the user, or it may end
     * the session if the current request has been completely satisified.
     * @param bool $shouldEnd True/false value to indicate if the session should be ended after
     * the current request.
     */
    public function setShouldEndSession($shouldEnd);

    /**
     * Returns the current value of the 'should end session' flag.
     * @return bool Returns true if the session should be ended.
     */
    public function getShouldEndSession();

    /**
     * Used to insert session attributes from Alexa into the current session data. 
     * @param array $attributes The JSON decoded attributes from the Alexa request.
     */
    public function setSessionAttributes($attributes);
    
    /**
     * Session attributes are an associative array (encoded into JSON) that are received from Alexa
     * and then sent back in the response (similar to cookies in HTTP). They are not persisted 
     * locally in Magento.
     * @return array Returns an associative array which can be modified by the caller. The array
     * is serialized to/from JSON. If not session attributes have been set, an empty array is
     * returned.
     */
    public function getSessionAttributes();

    /**
     * Similar to Alexa session attributes, but persisted in a Magento table. (The default
     * implementation currently discards the attributes.)
     * @param array $attributes The JSON decoded attributes from a local table.
     */
    public function setLocalAttributes($attributes);

    /**
     * Local attributes are similar to session attributes, but stored in a local database table.
     * (The default implemenation currently does not store these attributes.)
     * @return array Returns an associative array which can be modified by the caller.
     */
    public function getLocalAttributes();
}