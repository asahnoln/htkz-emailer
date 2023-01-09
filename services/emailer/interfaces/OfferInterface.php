<?php

namespace app\services\emailer\interfaces;

use yii\mail\MessageInterface;

interface OfferInterface
{
    public function findAndCompose(string $city): MessageInterface;
}
