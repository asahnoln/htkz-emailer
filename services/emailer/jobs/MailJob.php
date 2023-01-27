<?php

namespace app\services\emailer\jobs;

use yii\queue\JobInterface;

class MailJob implements JobInterface
{
    public function execute($queue): mixed
    {
    }
}
