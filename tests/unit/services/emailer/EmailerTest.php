<?php

namespace tests\unit\services\emailer;

use app\services\emailer\Emailer;
use app\services\emailer\interfaces\AnalyticsInterface;
use yii\mail\MailerInterface;
use yii\mail\MessageInterface;
use yii\symfonymailer\Message;

/**
 * @internal
 *
 * @coversNothing
 */
class EmailerTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // tests
    public function testSendFromQueue(): void
    {
        $m = new MailerSpy();
        $a = new AnalyticsStub();
        $baseMessage = new Message();

        $e = new Emailer($m, $a);
        $result = $e->send($baseMessage, 'd@a.a', '4');
        verify($result)->true();
        verify($m->sentMessages)->arrayCount(1);
        verify($m->sentMessages[0]->getTo())->arrayHasKey('d@a.a');
        // verify($m->sentMessages[0]->getSubject())->equals('hottest offer');
        // verify($baseMessage->getTextBody())->equals('hottest content');
        verify($a->ids[0])->equals('4');
    }

    public function testDontSendIfMailFail(): void
    {
        $m = new MailerSpy(failing: true);
        $a = new AnalyticsStub();
        $baseMessage = new Message();

        $e = new Emailer($m, $a);
        $result = $e->send($baseMessage, 'a@a.a', '');
        verify($result)->false();
    }

    protected function _before(): void
    {
    }

    protected function _after(): void
    {
    }

    // public function testEmailerInjection(): void
    // {
    //     $e = Yii::$container->get(Emailer::class);
    //     verify($e)->notNull();
    // }
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
        if ($this->failing) {
            return false;
        }

        $this->sentMessages[] = clone $message;

        return true;
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
