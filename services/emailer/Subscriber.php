<?php

namespace app\services\emailer;

class Subscriber
{
    public function __construct(public string $email, public string $id)
    {
    }
}
