<?php

namespace app\services\emailer;

class QueueMessage
{
    public bool $sent = false;

    public function __construct(public string $userId, public string $email, public string $title, public string $content)
    {
    }
}
