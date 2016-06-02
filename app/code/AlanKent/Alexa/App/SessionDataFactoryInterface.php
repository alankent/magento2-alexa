<?php

namespace AlanKent\Alexa\App;

/**
 * Factory for creating new SessionDataInterface instances.
 */
interface SessionDataFactoryInterface
{
    /**
     * Create a new SessionData instance.
     * @param string $sessionId The session id.
     * @return SessionDataInterface The newly created session data object.
     */
    public function create($sessionId);
}