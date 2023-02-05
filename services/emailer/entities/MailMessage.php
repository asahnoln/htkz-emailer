<?php

namespace app\services\emailer\entities;

class MailMessage
{
    // Статусы рассылки
    public const STATE_CREATED = 0;
    public const STATE_INPROGRESS = 1;
    public const STATE_DONE = 2;
}
