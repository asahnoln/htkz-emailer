<?php

namespace app\services\emailer;

// TODO: In future, we need an ID here as well I guess
/**
 * Сообщение оффера (заголовок, текст).
 */
class OfferMessage
{
    /**
     * @param string $title   Заголовок письма
     * @param string $content Содержимое письма
     */
    public function __construct(public string $title, public string $content)
    {
    }
}
