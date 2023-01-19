<?php

namespace tests\unit\services\emailer;

use app\services\emailer\interfaces\AudienceInterface;
use app\services\emailer\interfaces\OfferInterface;
use app\services\emailer\interfaces\QueueStoreInterface;
use app\services\emailer\OfferMessage;
use app\services\emailer\Queue;
use app\services\emailer\QueueMessage;
use app\services\emailer\Subscriber;

/**
 * @internal
 *
 * @coversNothing
 */
class QueueTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // tests
    public function testSendQueue(): void
    {
        // NOTE: Message inside is modified. Shouldn't be a big deal though...
        $qs = new QueueStoreStub();
        $q = new Queue($qs);
        $offer = new OfferStub();
        $audience = new AudienceStub();
        $qs->data = [];

        $result = $q->queueOfferToAudience('almaty', $offer, $audience);

        verify($result)->equals(3);
        verify($qs->data)->arrayCount(3);
        verify($qs->data[0]->email)->equals('a@a.a');
        verify($qs->data[0]->userId)->equals('1');
        verify($qs->data[0]->title)->equals('hot hot hot offer');
        verify($qs->data[0]->content)->equals('hot hot hot offer content');
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

    public function testFailingOfferShouldStopQueue(): void
    {
        $qs = new QueueStoreStub();
        $offer = new OfferStub(failing: true);
        $audience = new AudienceStub();

        $q = new Queue($qs);
        $result = $q->queueOfferToAudience('almaty', $offer, $audience);

        verify($result)->equals(0);
    }

    protected function _before(): void
    {
    }

    protected function _after(): void
    {
    }
}

class QueueStoreStub implements QueueStoreInterface
{
    /** @var QueueMessage[] */
    public array $data;

    public function __construct(private bool $failing = false, private bool $empty = false)
    {
        $this->data = [
            new QueueMessage('1', 'a@a.a', 'hot offer', 'hot content'),
            new QueueMessage('2', 'b@a.a', 'hot offer', 'hot content'),
            new QueueMessage('3', 'c@a.a', 'hottest offer', 'hottest content'),
            new QueueMessage('4', 'd@a.a', 'hottest offer', 'hottest content'),
        ];
    }

    public function send(QueueMessage $qm): bool
    {
        $this->data[] = $qm;

        return !$this->failing;
    }

    public function receive(): ?QueueMessage
    {
        if ($this->empty) {
            return null;
        }

        return array_pop($this->data);
    }

    public function finishState(QueueMessage $qm)
    {
    }
}

class OfferStub implements OfferInterface
{
    public function __construct(private bool $failing = false)
    {
    }

    public function find(string $city): ?OfferMessage
    {
        if ($this->failing) {
            return null;
        }

        return new OfferMessage('hot hot hot offer', 'hot hot hot offer content');
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
            'almaty' => [
                new Subscriber('a@a.a', '1'),
                new Subscriber('b@b.b', '2'),
                new Subscriber('c@c.c', '3'),
            ],
            '1' => [
                new Subscriber('a@a.a', '1'),
                new Subscriber('b@b.b', '2'),
                new Subscriber('c@c.c', '3'),
            ],
            '2' => [
                new Subscriber('d@a.a', '4'),
                new Subscriber('e@b.b', '5'),
            ],
            '3' => [
                new Subscriber('f@b.b', '6'),
            ],
        ][$city];
    }
}
