<?php

namespace app\services\emailer\interfaces;

use app\services\emailer\QueueMessage;

interface QueueStoreInterface
{
    public function send(QueueMessage $message): bool;
    public function receive(): ?QueueMessage;
}
