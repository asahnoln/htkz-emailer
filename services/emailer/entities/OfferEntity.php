<?php

namespace app\services\emailer\entities;

/**
 * Сообщение оффера (заголовок, текст).
 */
class OfferEntity
{
    /**
     * @param string $title   Заголовок письма
     * @param string $content Содержимое письма
     */
    public function __construct(public string $title, public string $content)
    {
    }
}
