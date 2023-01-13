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

    // tests
    public function testSendFromQueue(): void
    {
        $m = new MailerSpy();
        $a = new AnalyticsStub();
        $qs = new QueueStoreStub();
        $baseMessage = new Message();

        $e = new Emailer($m, $a);
        $result = $e->sendFromQueue($baseMessage, $qs);
        verify($result)->true();
        verify($m->sentMessages)->arrayCount(1);
        verify($m->sentMessages[0]->getTo())->arrayHasKey('d@a.a');
        verify($m->sentMessages[0]->getSubject())->equals('hottest offer');
        verify($baseMessage->getTextBody())->equals('hottest content');
        verify($a->ids[0])->equals('4');

        $e->sendFromQueue($baseMessage, $qs);
        verify($m->sentMessages)->arrayCount(2);
    }

    public function testDontSendFromEmptyQueue(): void
    {
        $m = new MailerSpy();
        $a = new AnalyticsStub();
        $qs = new QueueStoreStub(empty: true);
        $baseMessage = new Message();

        $e = new Emailer($m, $a);
        $result = $e->sendFromQueue($baseMessage, $qs);
        verify($result)->false();
        verify($m->sentMessages)->arrayCount(0);

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
