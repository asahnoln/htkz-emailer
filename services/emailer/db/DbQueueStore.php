<?php

namespace app\services\emailer\db;

use app\services\emailer\interfaces\QueueStoreInterface;
use app\services\emailer\QueueMessage;
use yii\db\Query;

/**
 * Очередь реализованная в БД.
 *
 * TODO: Исправить race condition
 */
class DbQueueStore implements QueueStoreInterface
{
    /**
     * {@inheritdoc}
     */
    public function send(QueueMessage $message): bool
    {
        \Yii::$app->db->createCommand()->insert('{{%mail_message}}', [
            'mail_id' => $message->userId,
            'title' => $message->title,
            'titleBig' => $message->title,
            'content' => $message->content,
            'site' => 1,
            'state' => 0,
            'is_sending' => 0,
            'chunk_sending_started_at' => '1970-01-01 00:00:00',
            'send_count' => 0,
            'error_count' => 0,
            'read_count' => 0,
            'site_visit_count' => 0,
            'previewEmail' => '',
            'addDate' => date('Y-m-d H:i:s'),
            'activationDate' => '1970-01-01 00:00:00',
            'startDate' => date('Y-m-d H:i:s'),
            'endDate' => date('Y-m-d H:i:s'),
            'custom_file' => '',
            'activationToken' => '',
        ])->execute();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function receive(): ?QueueMessage
    {
        $m = (new Query())
            ->from('{{%mail_message}} mm')
            ->select(['mm.id', 'mm.mail_id', 'm.email', 'mm.title', 'mm.content'])
            ->innerJoin('{{%mail}} m', 'm.id = mm.mail_id')
            ->where(['state' => 0])
            ->orderBy(['mm.addDate' => SORT_DESC, 'mm.id' => SORT_DESC])
            ->one()
        ;

        if (!$m) {
            return null;
        }

        \Yii::$app->db->createCommand()
            ->update('{{%mail_message}}', ['state' => 1], 'id = :id')->bindValue(':id', $m['id'])->execute();

        return new QueueMessage($m['mail_id'], $m['email'], $m['title'], $m['content'], $m['id']);
    }

    /**
     * {@inheritdoc}
     */
    public function finishState(QueueMessage $qm): void
    {
        $updateSql = 'UPDATE {{%mail_message}} SET state = :state';
        $state = 2;
        if (!$qm->sent) {
            $state = 3;
            $updateSql .= ', error_count = error_count + 1';
        }
        \Yii::$app->db->createCommand($updateSql.' WHERE id = :id')
            ->bindValues([':id' => $qm->id, ':state' => $state])->execute();
    }
}
