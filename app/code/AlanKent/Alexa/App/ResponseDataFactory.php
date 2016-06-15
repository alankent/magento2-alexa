<?php

namespace AlanKent\Alexa\App;

/**
 * Factory for creating ResponseData instances.
 */
class ResponseDataFactory
{
    /**
     * Create a new instance of a ResponseData instance.
     * @return ResponseData The newly created instance.
     */
    public function create()
    {
        return new ResponseData();
    }
}