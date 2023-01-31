<?php

namespace app\services\emailer\jobs;

use app\services\emailer\Emailer;
use app\services\emailer\entities\OfferEntity;
use app\services\emailer\entities\SubscriberEntity;
use yii\queue\JobInterface;
use yii\symfonymailer\Message;

class MailJob implements JobInterface
{
    public const STATE_CREATED = 0;
    public const STATE_INPROGRESS = 1;
    public const STATE_DONE = 2;

    public function __construct(private Emailer $emailer, private SubscriberEntity $sub, private OfferEntity $offer)
    {
    }

    public function execute($queue): void
    {
        $message = new Message();
        $message->setSubject($this->offer->title);
        $message->setTextBody($this->offer->content);
        $this->emailer->send($message, $this->sub->email, $this->sub->id);
        $this->changeLogState($this->sub->id);
    }

    public function changeLogState(int $id): void
    {
        $result = \Yii::$app->db->createCommand()
            ->update(
                '{{%mail_message}}',
                [
                    'state' => static::STATE_DONE,
                    'send_count' => 1,
                ],
                'mail_id = :mail_id and state = :state'
            )->bindValues([
                ':mail_id' => $id,
                ':state' => static::STATE_CREATED,
            ])->execute();

        codecept_debug($result);
    }
}
