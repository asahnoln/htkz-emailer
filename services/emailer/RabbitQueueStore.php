<?php

namespace app\services\emailer;

use app\services\emailer\interfaces\QueueStoreInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Сообщение очереди, содержащее данные для рассылки (почту, заголовк и т.д.).
 */
class RabbitQueueStore implements QueueStoreInterface
{
    public function __construct(private AMQPStreamConnection $amqp, private string $key)
    {
    }

    public function send(QueueMessage $message): bool
    {
        $chl = $this->amqp->channel();

        $amqpMsg = new AMQPMessage();
        $amqpMsg->setBody(serialize($message));

        $chl->basic_publish($amqpMsg, routing_key: $this->key);

        return true;
    }

    public function receive(): ?QueueMessage
    {
        $chl = $this->amqp->channel();
        $chl->queue_declare($this->key);

        $qm = null;
        $chl->basic_consume($this->key, callback: function (AMQPMessage $msg) use (&$qm) {
            $qm = unserialize($msg->body);
        });

        return $qm;
    }

    public function finishState(QueueMessage $qm): void
    {
    }
}
