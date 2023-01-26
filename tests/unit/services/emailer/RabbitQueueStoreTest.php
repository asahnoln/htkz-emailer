<?php

namespace services\emailer;

use app\services\emailer\interfaces\QueueStoreInterface;
use app\services\emailer\QueueMessage;
use app\services\emailer\RabbitQueueStore;
use Codeception\Stub\Expected;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @internal
 *
 * @coversNothing
 */
class RabbitQueueStoreTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    private $msgs = [];

    // tests
    public function testQueueSaves(): void
    {
        // $amqp = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->msgs = [];
        $amqp = $this->make(AMQPStreamConnection::class, [
            'channel' => Expected::atLeastOnce(function () {
                return $this->make(AMQPChannel::class, [
                    'basic_publish' => Expected::once(function (AMQPMessage $msg, string $exch, string $routingKey) {
                        verify($routingKey)->equals('mail');
                        $this->msgs[] = $msg;
                    }),
                ]);
            }),
        ]);
        $qs = new RabbitQueueStore($amqp, 'mail');
        verify($qs)->instanceOf(QueueStoreInterface::class);

        $qm1 = new QueueMessage('3', 'c@a.a', 'Some Offer', 'Offer Content Yay');
        $qs->send($qm1);
        $qm2 = new QueueMessage('4', 'test8@mail.com', 'Some Offer', 'Offer Content Yay');
        $qs->send($qm2);

        verify($this->msgs)->arrayCount(2);
        verify($this->msgs[0]->getBody())->stringContainsString('3');
        verify($this->msgs[0]->getBody())->stringContainsString('c@a.a');
        verify($this->msgs[0]->getBody())->stringContainsString('Some Offer');
        verify($this->msgs[0]->getBody())->stringContainsString('Offer Content Yay');
    }

    public function testQueueReceives(): void
    {
        $this->msgs = [];
        $amqp = $this->make(AMQPStreamConnection::class, [
            'channel' => Expected::atLeastOnce(function () {
                return $this->make(AMQPChannel::class, [
                    'basic_publish' => function (AMQPMessage $msg) {
                        $this->msgs[] = $msg;
                    },
                    // TODO: Cant really check for call because it's called 2 times in different methods...
                    'queue_declare' => (function (string $queue) {
                        verify($queue)->equals('mail');
                    }),
                    'basic_consume' => (function (
                        string $queue,
                        string $consumer_tag = '',
                        bool $no_local = false,
                        bool $no_ack = false,
                        bool $exclusive = false,
                        bool $nowait = false,
                        callable $callback = null,
                    ) {
                        verify($queue)->equals('mail');
                        verify($callback)->isCallable();
                        $msg = array_pop($this->msgs);
                        $callback($msg);
                    }),
                ]);
            }),
        ]);
        $qs = new RabbitQueueStore($amqp, 'mail');
        verify($qs)->instanceOf(QueueStoreInterface::class);

        $qm1 = new QueueMessage('3', 'c@a.a', 'Some Offer', 'Offer Content Yay');
        $qs->send($qm1);
        $qm2 = new QueueMessage('4', 'test8@mail.com', 'Some Offer', 'Offer Content Yay');
        $qs->send($qm2);

        $received = $qs->receive();
        $received->id = null; // Don't check id, it's dynamic
        verify($received)->equals($qm2);

        // $received = $qs->receive();
        // $received->id = null; // Don't check id, it's dynamic
        // verify($received)->equals($qm1);

        // verify($qs->receive())->null();
    }

    protected function _before(): void
    {
    }

    protected function _after(): void
    {
    }
}
