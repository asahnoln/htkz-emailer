<?php

namespace app\services\emailer;

use app\services\emailer\interfaces\AnalyticsInterface;
use yii\mail\MailerInterface;
use yii\mail\MessageInterface;

/**
 * Отправщик писем и аналитики.
 */
class Emailer
{
    /**
     * @param MailerInterface    $mailer    Объект, отправляющий письмо
     * @param AnalyticsInterface $analytics Объект, отправляющий аналитику
     */
    public function __construct(private MailerInterface $mailer, private AnalyticsInterface $analytics)
    {
    }

    /**
     * Отправить письмо и сохранить аналитику.
     *
     * @param MessageInterface $message Сообщение письма
     * @param string           $email   Почта
     * @param string           $id      Идентикифактор для аналитики
     *
     * @return bool Отправлено ли письмо
     */
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
