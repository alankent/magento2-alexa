<?php
/**
 * Created by PhpStorm.
 * User: akent
 * Date: 6/2/2016
 * Time: 1:21 AM
 */

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