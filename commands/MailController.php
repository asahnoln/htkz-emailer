<?php

namespace app\commands;

use app\services\emailer\OfferEmailer;
use yii\console\ExitCode;
use yii\queue\cli\Queue as CliQueue;

class MailController extends \yii\console\Controller
{
    public function actionPush(OfferEmailer $of, CliQueue $q): int
    {
        $of->push($q);

        return ExitCode::OK;
    }
}
