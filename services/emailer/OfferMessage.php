<?php

namespace app\services\emailer;

// TODO: In future, we need an ID here as well I guess
class OfferMessage
{
    public function __construct(public string $title, public string $content)
    {
    }
}
