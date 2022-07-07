<?php

namespace CNESIntegration\Connection;

class Conn
{
    public function __construct()
    {
        return new MongoDB\Client(
            '#'
        );
    }
}