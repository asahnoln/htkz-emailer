<?php

namespace app\services\emailer\interfaces;

/**
 * Сервис аналитики, принимающий запрос.
 */
interface AnalyticsInterface
{
    /**
     * Послать запрос в сервис аналитики с ID подписки.
     *
     * @param string $id ID подписки
     * @return bool Удачен ли запрос
     */
    public function send(string $id): bool;
}
