<?php
/**
 * Created by PhpStorm.
 * User: akent
 * Date: 6/2/2016
 * Time: 12:26 AM
 */

namespace AlanKent\Alexa\App;


/**
 * Default implementation of session data storage.
 */
class SessionData implements SessionDataInterface
{
    /** @var string */
    private $sessionId;

    /** @var string */
    private $lastTimestamp;

    /** @var bool */
    private $shouldEndSession;

    /** @var array Associative array to serialize as JSON for management by Alexa. */
    private $sessionAttributes;

    /** @var array Associative array to serialize as JSON for storage in local table. */
    private $localAttributes;

    /**
     * SessionData constructor.
     * @param string $sessionId The session ID for this session.
     * @param string $lastTimestamp Timestamp of previous session.
     */
    public function __construct($sessionId, $lastTimestamp) {
        $this->sessionId = $sessionId;
        $this->lastTimestamp = $lastTimestamp;
        $this->shouldEndSession = false;
        $this->sessionAttributes = array();
        $this->localAttributes = array();
    }

    /**
     * @inheritdoc
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @inheritdoc
     */
    public function getLastTimestamp()
    {
        return $this->lastTimestamp;
    }

    /**
     * @inheritdoc
     */
    public function setShouldEndSession($shouldEndSession)
    {
        $this->shouldEndSession = $shouldEndSession;
    }

    /**
     * @inheritdoc
     */
    public function getShouldEndSession()
    {
        return $this->shouldEndSession;
    }

    /**
     * @inheritdoc
     */
    public function setSessionAttributes($attributes)
    {
        $this->sessionAttributes = $attributes;
    }

    /**
     * @inheritdoc
     */
    public function getSessionAttributes()
    {
        return $this->sessionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setLocalAttributes($attributes)
    {
        $this->localAttributes = $attributes;
    }

    /**
     * @inheritdoc
     */
    public function getLocalAttributes()
    {
        return $this->localAttributes;
    }
}