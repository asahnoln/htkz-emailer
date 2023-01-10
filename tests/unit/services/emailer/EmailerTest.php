<?php

namespace tests\unit\services\emailer;

use app\services\emailer\Emailer;
use app\services\emailer\interfaces\AnalyticsInterface;
use yii\mail\MailerInterface;
use yii\mail\MessageInterface;
use yii\symfonymailer\Message;

class EmailerTest extends \Codeception\Test\Unit
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

    // TODO: Save to DB first, then send
    // tests
    public function testSendFromQueue(): void
    {
        $m = new MailerSpy();
        $a = new AnalyticsStub();
        $e = new Emailer($m, $a);
        $qs = new QueueStoreStub();

        $e->sendFromQueue($qs);
        // verify($m->sentMessages)->arrayCount(3);
        // verify($mailer->sentMessages[0]->getTo())->arrayHasKey('a@a.a');
        // verify($mailer->sentMessages[0]->getSubject())->equals('test offer');
    }
}

class MailerSpy implements MailerInterface
{
    /** @var MessageInterface[] */
    public array $sentMessages = [];

    public function __construct(private bool $failing = false)
    {
    }

    public function compose($view = null, array $params = []): MessageInterface
    {
        return new Message();
    }

    public function send($message): bool
    {
        $this->sentMessages[] = clone $message;
        return !$this->failing;
    }

    public function sendMultiple(array $messages): int
    {
        return 0;
    }
}

class AnalyticsStub implements AnalyticsInterface
{
    /** @var int[] */
    public array $ids = [];
    public function send(string $id): bool
    {
        $this->ids[] = $id;
        return true;
    }
}
