<?php

namespace app\services\emailer\jobs;

use app\services\emailer\Emailer;
use yii\queue\JobInterface;
use yii\symfonymailer\Message;

class MailJob implements JobInterface
{
    public function __construct(private Emailer $emailer, private string $email, private string $id)
    {
    }

    public function execute($queue): mixed
    {
        $message = new Message();
        $this->emailer->send($message, $this->email, $this->id);

        return false;
    }
}
