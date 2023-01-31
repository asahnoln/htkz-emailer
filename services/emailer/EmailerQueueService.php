<?php

namespace app\services\emailer;

use app\services\emailer\interfaces\OfferInterface;
use app\services\emailer\jobs\MailJob;
use app\services\emailer\repositories\AudienceRepository;
use app\services\emailer\repositories\CityRepository;
use yii\queue\cli\Queue as CliQueue;

class EmailerQueueService
{
    private Emailer $emailer;
    private CliQueue $queue;
    private OfferInterface $offer;

    public function __construct()
    {
        $this->emailer = \Yii::$container->get(Emailer::class);
        $this->queue = \Yii::$container->get(CliQueue::class);
        $this->offer = \Yii::$container->get(OfferInterface::class);
    }

    public function push(): void
    {
        $cities = (new CityRepository())->findAll();
        $ar = new AudienceRepository();

        foreach ($cities as $city) {
            $offer = $this->offer->find($city['id']);
            codecept_debug($offer);
            // if (!$offer) {
            //     return;
            // }

            foreach ($ar->findAll($city['id']) as $sub) {
                $this->queue->push(new MailJob($this->emailer, $sub->email, $sub->id));
                $this->logToDb($sub->id, $offer->title, $offer->content);
            }
        }
    }

    protected function logToDb(int $mailId, string $title, string $content): void
    {
        $date = (new \DateTime())->format('Y-m-d H:i:s');
        \Yii::$app->db->createCommand()->insert('{{%mail_message}}', [
            'mail_id' => $mailId,
            'title' => $title,
            'titleBig' => $title,
            'content' => $content,
            'site' => 1,
            'state' => 0,
            'is_sending' => 0,
            'chunk_sending_started_at' => '1970-01-01 00:00:00',
            'send_count' => 0,
            'error_count' => 0,
            'read_count' => 0,
            'site_visit_count' => 0,
            'previewEmail' => '',
            'addDate' => $date,
            'activationDate' => '1970-01-01 00:00:00',
            'startDate' => $date,
            'endDate' => $date,
            'custom_file' => '',
            'activationToken' => '',
        ])->execute();
    }
}
