<?php

namespace app\services\emailer\interfaces;

use app\services\emailer\entities\OfferEntity;

/**
 * Оффер для рассылки аудитории. Находится по городу.
 */
interface OfferInterface
{
    /**
     * Найти оффер по городу.
     *
     * @param int $city Город оффера (идентификатор)
     *
     * @return ?OfferEntity Сообщение оффера или null
     */
    public function find(int $city): ?OfferEntity;
}
