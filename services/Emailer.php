<?php

namespace app\services;

use yii\mail\MailerInterface;
use yii\symfonymailer\Message;

class Emailer
{
    public function __construct(private MailerInterface $mailer, private AnalyticsInterface $analytics)
    {
    }

    public function send(): bool
    {
        $message = new Message();
        $message->setTo('arthur@example.com');

        $this->mailer->send($message);
        return true;
    }
}
