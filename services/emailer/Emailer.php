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

    /**
     * @return bool
     */
    public function sendFromQueue(MessageInterface $message, QueueStoreInterface $queue): bool
    {
        if ($qm = $queue->receive()) {
            $message->setSubject($qm->title);
            $message->setTextBody($qm->content);
            return $this->send($message, $qm->email, $qm->userId);
        }

        return false;
    }

    public function send(MessageInterface $message, string $email, string $id): bool
    {
        $message->setTo($email);

        if ($this->mailer->send($message)) {
            $this->analytics->send($id);
            return true;
        }

        return false;
    }
}
