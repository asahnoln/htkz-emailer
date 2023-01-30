<?php

namespace app\services\emailer;

use app\services\emailer\db\AudienceRepository;
use app\services\emailer\db\CityRepository;
use app\services\emailer\jobs\MailJob;
use yii\queue\cli\Queue as CliQueue;

class EmailerQueueService
{
    private Emailer $emailer;
    private CliQueue $queue;

    public function __construct()
    {
        $this->emailer = \Yii::$container->get(Emailer::class);
        $this->queue = \Yii::$container->get(CliQueue::class);
    }

    public function push(): void
    {
        $cities = (new CityRepository())->findAll();
        $a = new AudienceRepository();

        foreach ($cities as $c) {
            foreach ($a->findAll($c['id']) as $sub) {
                $this->queue->push(new MailJob($this->emailer, $sub->email, $sub->id));
            }
        }
    }
}
