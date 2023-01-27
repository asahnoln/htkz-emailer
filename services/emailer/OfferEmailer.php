<?php

namespace app\services\emailer;

use app\services\emailer\db\AudienceRepository;
use yii\queue\cli\Queue as CliQueue;
use yii\queue\JobInterface;

class OfferEmailer
{
    public function push(CliQueue $q): void
    {
        $cities = \Yii::$app->db->createCommand('SELECT * FROM {{%city}}')->queryAll();
        $a = new AudienceRepository();

        foreach ($cities as $c) {
            foreach ($a->findAll($c['id']) as $sub) {
                $q->push(new MailJob());
            }
        }
    }
}

class MailJob implements JobInterface
{
    public function execute($queue): mixed
    {
    }
}
