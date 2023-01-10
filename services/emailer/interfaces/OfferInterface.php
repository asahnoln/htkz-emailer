<?php

namespace app\services\emailer\interfaces;

use app\services\emailer\OfferMessage;

interface OfferInterface
{
    public function find(string $city): OfferMessage;
}
