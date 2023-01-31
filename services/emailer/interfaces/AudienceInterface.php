<?php

namespace app\services\emailer\interfaces;

/**
 * Аудитория, которой нужно рассылать письма. Находится по городу.
 */
interface AudienceInterface
{
    /**
     * Найти всех подписчиков по городу.
     *
     * @param int $city Город (идентификатор)
     *
     * @return array<int,app\services\emailer\Subscriber>
     */
    public function findAll(int $city): array;
}
