<?php
namespace AlanKent\Alexa\App;

/**
 * Default implementation of CustomerDataInterface.
 */
class CustomerData implements CustomerDataInterface
{
    /** @var string */
    private $userId;

    /** @var string */
    private $accessToken;

    /** @var array */
    private $localAttributes;

    /**
     * CustomerData constructor.
     * @param string $userId
     * @param string $accessToken
     */
    public function __construct($userId, $accessToken)
    {
        $this->userId = $userId;
        $this->accessToken = $accessToken;
        $this->localAttributes = array();
    }

    /**
     * @inheritdoc
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @inheritdoc
     */
    public function getAccessToken()
    {
        return $this->accessToken;
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