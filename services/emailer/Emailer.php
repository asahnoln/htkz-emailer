<?php

namespace app\services\emailer;

use app\services\emailer\interfaces\AnalyticsInterface;
use app\services\emailer\interfaces\AudienceInterface;
use app\services\emailer\interfaces\OfferInterface;
use yii\mail\MailerInterface;
use yii\mail\MessageInterface;
use yii\symfonymailer\Message;

class Emailer
{
    public function __construct(private MailerInterface $mailer, private AnalyticsInterface $analytics)
    {
    }

    public function sendOfferToAudience(string $city, OfferInterface $offer, AudienceInterface $audience): int
    {
        $count = 0;
        $offerMessage = $offer->findAndCompose($city);
        /** @var Subscriber $sub */
        foreach ($audience->findAll($city) as $sub) {
            if ($this->send($offerMessage, $sub->email, $sub->id)) {
                $count++;
            }
        }
        return $count;
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
