<?php

namespace app\services;

interface AnalyticsInterface
{
    /**
     * @return void
     */
    public function send(): bool;
}
