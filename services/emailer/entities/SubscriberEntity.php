<?php

namespace app\services\emailer\entities;

/**
 * Подписчик на рассылку из аудитории.
 */
class SubscriberEntity
{
    /**
     * @param string $email Email подписчика
     * @param string $id    ID подписки
     */
    public function __construct(public string $email, public string $id)
    {
    }
}
