<?php

namespace AlanKent\Alexa\App;

/**
 * Factory for default class implementation of customer data.
 */
class CustomerDataFactory implements CustomerDataFactoryInterface
{
    /**
     * Create a new CustomerData instance.
     * @param string $userId The user id.
     * @param string|null $accessToken The optional access token.
     * @return CustomerDataInterface The newly created customer data object.
     */
    public function create($userId, $accessToken)
    {
        return new CustomerData($userId, $accessToken);
    }
}