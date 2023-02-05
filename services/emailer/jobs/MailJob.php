<?php

namespace app\services\emailer\jobs;

use app\services\emailer\Emailer;
use yii\queue\JobInterface;

/**
 * Задание отправки письма.
 */
class MailJob implements JobInterface
{
    // Статусы рассылки
    public const STATE_CREATED = 0;
    public const STATE_INPROGRESS = 1;
    public const STATE_DONE = 2;

    private Emailer $emailer;

    /**
     * @param array<int,mixed> $offerPayload
     */
    public function __construct(private string $subEmail, private int $subId, private string $offerTitle, private array $offerPayload)
    {
        $this->emailer = \Yii::$container->get(Emailer::class);
    }

    /**
     * {@inheritdoc}
     */
    public function execute($queue): void
    {
        $message = \Yii::$app->mailer->compose('offer', ['content' => $this->offerPayload]);
        $message->setSubject($this->offerTitle);
        $this->emailer->send($message, $this->subEmail, $this->subId);
        $this->changeLogState($this->subId);
    }

    /**
     * Поменять статус отправки в логах БД.
     *
     * @param int $mailId ID подписчика
     */
    public function changeLogState(int $mailId): void
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
                ':mail_id' => $mailId,
                ':state' => static::STATE_CREATED,
            ])->execute();
    }
}
