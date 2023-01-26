<?php

namespace app\commands;

use yii\console\ExitCode;
use yii\queue\JobInterface;
use yii\queue\cli\Queue as CliQueue;

class MailController extends \yii\console\Controller
{
    public function actionPush(CliQueue $q): int
    {
        $q->push(new MailJob());
        $q->push(new MailJob());
        return ExitCode::OK;
    }
}

class MailJob implements JobInterface
{
    public function execute($queue): mixed
    {
    }
}
