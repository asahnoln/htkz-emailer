<?php

namespace app\services\emailer;

use app\services\emailer\db\AudienceRepository;
use yii\queue\cli\Queue as CliQueue;
use yii\queue\JobInterface;

class OfferEmailer
{
    public function push(CliQueue $q): void
    {
        $a = new AudienceRepository();

        foreach ($a->findAll(1) as $sub) {
            $q->push(new MailJob());
        }
    }
}

class MailJob implements JobInterface
{
    public function execute($queue): mixed
    {
    }
}
