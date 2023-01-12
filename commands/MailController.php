<?php

namespace app\commands;

use app\services\emailer\Emailer;
use app\services\emailer\interfaces\QueueStoreInterface;
use yii\console\ExitCode;
use yii\mail\MessageInterface;

class MailController extends \yii\console\Controller
{
    public function actionSend(Emailer $emailer, MessageInterface $message, QueueStoreInterface $queue): int
    {
        while ($emailer->sendFromQueue($message, $queue)) {
        }

        return ExitCode::OK;
    }
}
