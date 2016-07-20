<?php

namespace AlanKent\Alexa\App;

/**
 * Used to store customer specific data that should live longer than a session.
 * For example, purchase history.
 */
interface CustomerDataInterface
{
    /**
     * Return the user id for the current user.
     * @return string The user id.
     */
    public function getUserId();

    /**
     * Return the access token if Alexa authenticated the user by an external authentication source
     * (as defined by Alexa application configuration settings).
     * @return string|null Returns access token, if provided by Alexa.
     */
    public function getAccessToken();

    /**
     * Similar to session local attributes, but for customer data. This data lives
     * beyond the lifetime of a session, which is relatively short lived.
     * (The current default implementation does not persist these attributes.)
     * @param array $attributes The JSON decoded attributes from a local table.
     */
    public function setLocalAttributes($attributes);

    /**
     * Similar to session local attributes, but for customer data.
     * (The default implementation currently does not store these attributes.)
     * @return array Returns an associative array which can be modified by the caller.
     */
    public function getLocalAttributes();
}