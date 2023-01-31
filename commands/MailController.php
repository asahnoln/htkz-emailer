<?php

namespace app\commands;

use app\services\emailer\EmailerQueueService;
use yii\console\ExitCode;

class MailController extends \yii\console\Controller
{
    public function actionPush(): int
    {
        (new EmailerQueueService())->push();

        return ExitCode::OK;
    }
}
