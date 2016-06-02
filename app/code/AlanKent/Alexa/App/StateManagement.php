<?php
/**
 * Created by PhpStorm.
 * User: akent
 * Date: 6/2/2016
 * Time: 12:10 AM
 */

namespace AlanKent\Alexa\App;


/**
 * @inheritdoc
 */
class StateManagement implements StateManagementInterface
{
    /** @var SessionDataFactoryInterface */
    private $sessionDataFactory;

    /** @var CustomerDataFactoryInterface */
    private $customerDataFactory;

    /**
     * StateManagement constructor.
     * @param SessionDataFactoryInterface $sessionDataFactory
     * @param CustomerDataFactoryInterface $customerDataFactory
     */
    public function __construct($sessionDataFactory, $customerDataFactory) {
        $this->sessionDataFactory = $sessionDataFactory;
        $this->customerDataFactory = $customerDataFactory;
    }
    
    /**
     * @inheritdoc
     */
    public function createNewSession($sessionId)
    {
        return $this->sessionDataFactory->create($sessionId);
    }

    /**
     * @inheritdoc
     */
    public function retrieveSessionData($sessionId)
    {
        return $this->sessionDataFactory->create($sessionId);
    }

    /**
     * @inheritdoc
     */
    public function retrieveCustomerData($userId, $accessToken)
    {
        return $this->customerDataFactory->create($userId, $accessToken);
    }

    /**
     * @inheritdoc
     */
    public function saveSessionUpdates($sessionData, $timestamp)
    {
        // Default behavior is to not persist updates.
    }

    /**
     * @inheritdoc
     */
    public function saveCustomerUpdates($customerData)
    {
        // Default behavior is to not persist updates.
    }
}