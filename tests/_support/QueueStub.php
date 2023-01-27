<?php

use yii\queue\cli\Queue;

class QueueStub extends Queue
{
    public array $msgs = [];

    public function status($id): int
    {
        return Queue::STATUS_DONE;
    }

    protected function pushMessage($message, $ttr, $delay, $priority): string
    {
        return array_push($this->msgs, $message) - 1;
    }
}
