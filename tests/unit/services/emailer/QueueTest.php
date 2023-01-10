<?php

namespace tests\unit\services\emailer;

use app\services\emailer\OfferMessage;
use app\services\emailer\Queue;
use app\services\emailer\QueueMessage;
use app\services\emailer\Subscriber;
use app\services\emailer\interfaces\AudienceInterface;
use app\services\emailer\interfaces\OfferInterface;
use app\services\emailer\interfaces\QueueStoreInterface;
use yii\mail\MessageInterface;
use yii\symfonymailer\Message;

class QueueTest extends \Codeception\Test\Unit
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
    public function testSendQueue(): void
    {
        // NOTE: Message inside is modified. Shouldn't be a big deal though...
        $qs = new QueueStoreStub();
        $q = new Queue($qs);
        $offer = new OfferStub();
        $audience = new AudienceStub();

        $result = $q->queueOfferToAudience('almaty', $offer, $audience);

        verify($result)->equals(3);
        verify($qs->data)->arrayCount(3);
        verify($qs->data[0]->email)->equals('a@a.a');
        verify($qs->data[0]->userId)->equals('1');
        verify($qs->data[0]->title)->equals('hot test offer');
        verify($qs->data[0]->content)->equals('hot test offer content');
    }

    public function testFailingStoreShouldNotCount(): void
    {
        $qs = new QueueStoreStub(failing: true);
        $offer = new OfferStub();
        $audience = new AudienceStub();

        $q = new Queue($qs);
        $result = $q->queueOfferToAudience('almaty', $offer, $audience);

        verify($result)->equals(0);
    }
}

class QueueStoreStub implements QueueStoreInterface
{
    /** @var QueueMessage[] */
    public array $data = [];

    public function __construct(private bool $failing = false)
    {
    }

    public function send(QueueMessage $qm): bool
    {
        $this->data[] = $qm;
        return !$this->failing;
    }

    public function receive(): QueueMessage
    {
    }
}

class OfferStub implements OfferInterface
{
    public function find(string $city): OfferMessage
    {
        return (new OfferMessage('hot offer', 'hot offer content'));
    }
}

class AudienceStub implements AudienceInterface
{
    /**
     * @return array<int,Subscriber>
     */
    public function findAll(string $city): array
    {
        return [
            new Subscriber('a@a.a', '1'),
            new Subscriber('b@b.b', '2'),
            new Subscriber('c@c.c', '3'),
        ];
    }
}
