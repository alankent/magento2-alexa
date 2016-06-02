<?php

namespace AlanKent\Alexa\App;

/**
 * Factory for creating new CustomerDataInterface instances.
 */
interface CustomerDataFactoryInterface
{
    /**
     * Create a new CustomerData instance.
     * @param string $userId The user id.
     * @param string|null $accessToken The optional access token.
     * @return CustomerDataInterface The newly created customer data object.
     */
    public function create($userId, $accessToken);
}