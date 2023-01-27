<?php

namespace app\commands;

use app\services\emailer\OfferEmailer;
use yii\console\ExitCode;
use yii\queue\cli\Queue as CliQueue;

class MailController extends \yii\console\Controller
{
    public function actionPush(CliQueue $q): int
    {
        (new OfferEmailer())->push($q);

        return ExitCode::OK;
    }
}
