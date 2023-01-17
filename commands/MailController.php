<?php

namespace app\commands;

use Yii;
use app\services\emailer\Emailer;
use app\services\emailer\Queue;
use app\services\emailer\interfaces\AudienceInterface;
use app\services\emailer\interfaces\OfferInterface;
use app\services\emailer\interfaces\QueueStoreInterface;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\mail\MessageInterface;

class MailController extends \yii\console\Controller
{
    public function actionSend(Emailer $emailer, MessageInterface $message, QueueStoreInterface $queue, AudienceInterface $audience, OfferInterface $offer): int
    {
        $cities = Yii::$app->db->createCommand('SELECT * FROM {{%city}}')->queryAll();

        $q = new Queue($queue);
        foreach ($cities as $city) {
            $q->queueOfferToAudience($city['id'], $offer, $audience);
        }

        while ($qm = $emailer->sendFromQueue($message, $queue)) {
            $sent = $qm->sent ? 'has been sent' : 'has not been sent';
            $settings = $qm->sent ? [Console::FG_GREEN] : [Console::BG_RED, Console::BOLD];
            $this->stdout("A letter to {$qm->email} {$sent}\n", ...$settings);
        }

        return ExitCode::OK;
    }
}
