<?php

namespace app\services\emailer\interfaces;

interface AnalyticsInterface
{
    /**
     * @return void
     */
    public function send(string $id): bool;
}
