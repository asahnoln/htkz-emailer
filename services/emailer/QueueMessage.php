<?php

namespace app\services\emailer;

/**
 * Сообщение очереди, содержащее данные для рассылки (почту, заголовк и т.д.).
 */
class QueueMessage
{
    /** @var bool Отправлено ли сообщение по почте */
    public bool $sent = false;

    /**
     * @param string $userId  ID подписки
     * @param string $email   Email подписчика
     * @param string $title   Заголовок письма
     * @param string $content Содержимое письма
     */
    public function __construct(public string $userId, public string $email, public string $title, public string $content)
    {
    }
}
