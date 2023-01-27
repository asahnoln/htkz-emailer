<?php

namespace app\services\emailer\interfaces;

use app\services\emailer\QueueMessage;

/**
 * Источник очереди. Сохраняет и получает сообщения.
 */
interface QueueStoreInterface
{
    /**
     * Послать сообщение в очередь.
     *
     * @param QueueMessage $message Сообщение с данными
     *
     * @return bool Успешно ли передано сообщение
     */
    public function send(QueueMessage $message): bool;

    /**
     * Получить сообщение из очереди.
     *
     * @return ?QueueMessage Сообщение или null в случае пустой/недоступной очереди
     */
    public function receive(): ?QueueMessage;

    /**
     * Пометить состояние сообщения как завершенное.
     */
    public function finishState(QueueMessage $qm): void;
}
