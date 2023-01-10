<?php

namespace app\services\emailer;

use app\services\emailer\interfaces\AnalyticsInterface;
use app\services\emailer\interfaces\QueueStoreInterface;
use yii\mail\MailerInterface;
use yii\mail\MessageInterface;

class Emailer
{
    public function __construct(private MailerInterface $mailer, private AnalyticsInterface $analytics)
    {
    }

    public function sendFromQueue(QueueStoreInterface $queue)
    {
    }

    public function send(MessageInterface $message, string $email, string $id): bool
    {
        $message->setTo($email);

        if ($this->mailer->send($message)) {
            $this->analytics->send('testId');
            return true;
        }

        return false;
    }
}
