<?php
/**
 * Created by PhpStorm.
 * User: akent
 * Date: 6/2/2016
 * Time: 1:17 AM
 */

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
     * @param string $acccessToken
     */
    public function __construct($userId, $acccessToken) {
        $this->userId = $userId;
        $this->accessToken = $acccessToken;
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