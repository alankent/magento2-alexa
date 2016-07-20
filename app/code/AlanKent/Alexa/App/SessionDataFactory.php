<?php

namespace AlanKent\Alexa\App;

/**
 * Factory to create default SessionDataInterface instances.
 */
class SessionDataFactory implements SessionDataFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function create($sessionId)
    {
        return new SessionData($sessionId, null);
    }
}