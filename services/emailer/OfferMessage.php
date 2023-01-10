<?php

namespace app\services\emailer;

class OfferMessage
{
    public function __construct(public string $title, public string $content)
    {
    }
}
