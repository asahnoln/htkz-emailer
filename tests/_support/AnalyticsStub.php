<?php

use app\services\emailer\interfaces\AnalyticsInterface;

class AnalyticsStub implements AnalyticsInterface
{
    /** @var int[] */
    public array $ids = [];

    public function send(string $id): bool
    {
        $this->ids[] = $id;

        return true;
    }
}
