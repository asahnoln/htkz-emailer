<?php

namespace app\services\emailer\db;

use app\services\emailer\interfaces\QueueStoreInterface;
use app\services\emailer\QueueMessage;
use yii\db\Query;

class DbQueueStore implements QueueStoreInterface
{
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

        return new QueueMessage($m['mail_id'], $m['email'], $m['title'], $m['content']);
    }
}
