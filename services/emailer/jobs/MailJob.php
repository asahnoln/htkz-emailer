<?php

namespace app\services\emailer\jobs;

use app\services\emailer\Emailer;
use app\services\emailer\entities\MailMessage;
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
    public function __construct(public int $mailMessageId, public string $subEmail, public int $subId, public string $offerTitle, public array $offerPayload)
    {
        $this->emailer = \Yii::$container->get(Emailer::class);
    }

    /**
     * {@inheritdoc}
     */
    public function execute($queue): void
    {
        if (!$this->isMailMessageInProgress()) {
            return;
        }

        $message = \Yii::$app->mailer->compose('offer', ['content' => $this->offerPayload]);
        $message->setSubject($this->offerTitle);
        $this->emailer->send($message, $this->subEmail, $this->subId);
        $this->countMailMessageSent();
    }

    /**
     * Поменять статус отправки в логах БД.
     *
     * @param int $mailId ID подписчика
     */
    public function countMailMessageSent(): void
    {
        $result = \Yii::$app->db
            ->createCommand('UPDATE {{%mail_message}} SET send_count = send_count + 1 WHERE id = :id')
            ->bindValues([
                ':id' => $this->mailMessageId,
            ])->execute();
    }

    protected function isMailMessageInProgress(): bool
    {
        return \Yii::$app->db
            ->createCommand('SELECT COUNT(*) FROM {{%mail_message}} WHERE id = :id AND state = :state')
            ->bindValues([
                ':id' => $this->mailMessageId,
                ':state' => MailMessage::STATE_INPROGRESS,
            ])->queryScalar() > 0;
    }
}
