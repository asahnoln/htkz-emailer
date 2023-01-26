<?php

namespace app\commands;

use app\services\emailer\Emailer;
use app\services\emailer\db\DbAudience;
use app\services\emailer\db\DbOffer;
use app\services\emailer\db\DbQueueStore;
use yii\symfonymailer\Message;

class MailController extends \yii\console\Controller
{
    public function actionPush(Emailer $e, Message $msg, DbQueueStore $q, DbAudience $a, DbOffer $o): void
    {
    }
}
