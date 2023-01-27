<?php

namespace app\services\emailer;

use app\services\emailer\db\AudienceRepository;
use app\services\emailer\db\CityRepository;
use app\services\emailer\jobs\MailJob;
use yii\queue\cli\Queue as CliQueue;

class OfferEmailer
{
    public function push(CliQueue $q): void
    {
        $cities = (new CityRepository())->findAll();
        $a = new AudienceRepository();

        foreach ($cities as $c) {
            foreach ($a->findAll($c['id']) as $sub) {
                $q->push(new MailJob());
            }
        }
    }
}
