<?php

namespace app\services\emailer;

use app\services\emailer\interfaces\OfferInterface;
use app\services\emailer\jobs\MailJob;
use app\services\emailer\repositories\AudienceRepository;
use app\services\emailer\repositories\CityRepository;
use yii\queue\cli\Queue as CliQueue;

/**
 * Сервис постановки отправки письма в очередь.
 */
class EmailerQueueService
{
    private CliQueue $queue;
    private OfferInterface $offer;

    public function __construct()
    {
        $this->queue = \Yii::$container->get(CliQueue::class);
        $this->offer = \Yii::$container->get(OfferInterface::class);
    }

    /**
     * Отправить в очередь джобу на отправку письма.
     */
    public function push(): void
    {
        $cities = (new CityRepository())->findAll();
        $ar = new AudienceRepository();

        foreach ($cities as $city) {
            $offer = $this->offer->findByCity($city['id']);
            if (!$offer) {
                return;
            }

            $this->createMailMessage($offer->title, serialize($offer->payload));
            foreach ($ar->findAll($city['id']) as $sub) {
                $this->queue->push(new MailJob($sub->email, $sub->id, $offer->title, $offer->payload));
            }
        }
    }

    /**
     * Логгировать в БД отсылку почты.
     *
     * @param int    $mailId  ID подписчика
     * @param string $title   Заголовок письма
     * @param string $content Контент письма
     */
    protected function createMailMessage(string $title, string $content): void
    {
        $date = (new \DateTime())->format('Y-m-d H:i:s');
        \Yii::$app->db->createCommand()->insert('{{%mail_message}}', [
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
