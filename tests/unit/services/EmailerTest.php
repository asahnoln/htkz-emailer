<?php

namespace services;

use app\services\emailer\Subscriber;
use app\services\emailer\interfaces\AnalyticsInterface;
use app\services\emailer\interfaces\AudienceInterface;
use app\services\emailer\Emailer;
use app\services\emailer\interfaces\OfferInterface;
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
    public function testSendsOfferToAudience(): void
    {
        // NOTE: Message inside is modified. Shouldn't be a big deal though...
        //
        $mailer = new MailerSpy();
        $analytics = new AnalyticsStub();
        $offer = new OfferStub();
        $audience = new AudienceStub();

        $e = new Emailer($mailer, $analytics);
        $result = $e->sendOfferToAudience('almaty', $offer, $audience);

        verify($result)->equals(3);
        verify($mailer->sentMessages)->arrayCount(3);
        verify($mailer->sentMessages[0]->getTo())->arrayHasKey('a@a.a');
        verify($mailer->sentMessages[0]->getSubject())->equals('test offer');
    }

    public function testFailingMailShouldNotCount(): void
    {
        $mailer = new MailerSpy(true);
        $analytics = new AnalyticsStub();
        $offer = new OfferStub();
        $audience = new AudienceStub();

        $e = new Emailer($mailer, $analytics);
        $result = $e->sendOfferToAudience('almaty', $offer, $audience);

        verify($result)->equals(0);
    }
}

class MailerSpy implements MailerInterface
{
    public MessageInterface $message;

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
        $this->message = $message;
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
    public ?string $id = null;
    public function send(string $id): bool
    {
        return $this->id = $id;
    }
}

class OfferStub implements OfferInterface
{
    public function findAndCompose(string $city): MessageInterface
    {
        $m = new Message();
        $m->setSubject('test offer');
        return $m;
    }
}

class AudienceStub implements AudienceInterface
{
    public function findAll(string $city): array
    {
        return [
            new Subscriber('a@a.a', '1'),
            new Subscriber('b@b.b', '2'),
            new Subscriber('c@c.c', '3'),
        ];
    }
}
