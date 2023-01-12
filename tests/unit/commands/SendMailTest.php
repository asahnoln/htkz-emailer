<?php

namespace tests\unit\commands;

use app\commands\MailController;
use app\services\emailer\Emailer;
use tests\unit\services\emailer\AnalyticsStub;
use tests\unit\services\emailer\MailerSpy;
use tests\unit\services\emailer\QueueStoreStub;
use yii\console\ExitCode;
use yii\symfonymailer\Message;

class SendMailTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
    }

    protected function _after(): void
    {
    }

    // tests
    public function testSendMailCommand(): void
    {
        // Static check for function in the controller
        // $result = Yii::$app->createControllerByID('mail')->run('send');
        // verify($result)->equals(ExitCode::OK);

        $mc = new MailController('', '');
        $m = new MailerSpy();
        $a = new AnalyticsStub();

        $e = new Emailer($m, $a);
        $msg = new Message();
        $q = new QueueStoreStub();
        $qSize = count($q->preparedQueue);

        $code = $mc->actionSend($e, $msg, $q);

        verify($code)->isInt();
        verify($code)->equals(ExitCode::OK);

        verify($m->sentMessages)->arrayCount($qSize);
    }
}
