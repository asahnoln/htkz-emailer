<?php

namespace app\services\emailer\interfaces;

use app\services\emailer\OfferMessage;

/**
 * Оффер для рассылки аудитории. Находится по городу.
 */
interface OfferInterface
{
    /**
     * Найти оффер по городу.
     *
     * @param string $city Город оффера (идентификатор)
     * @return ?OfferMessage Сообщение оффера или null
     */
    public function find(string $city): ?OfferMessage;
}
