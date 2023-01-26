<?php

namespace app\commands;

use yii\console\ExitCode;

class MailController extends \yii\console\Controller
{
    public function actionPush(): int
    {
        return ExitCode::OK;
    }
}
