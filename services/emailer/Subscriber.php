<?php

namespace app\services\emailer;

/**
 * Подписчик на рассылку из аудитории.
 */
class Subscriber
{
    /**
     * @param string $email Email подписчика
     * @param string $id    ID подписки
     */
    public function __construct(public string $email, public string $id)
    {
    }
}
