<?php

namespace AlanKent\Alexa\App;

/**
 * Default state management implementation, which does not preserve any state.
 * Other implementations can be provided to save state say in a database table.
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
    public function __construct(
        SessionDataFactoryInterface $sessionDataFactory,
        CustomerDataFactoryInterface $customerDataFactory
    ) {
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