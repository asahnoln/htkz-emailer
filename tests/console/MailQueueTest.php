<?php

namespace commands;

use yii\console\ExitCode;
use yii\queue\cli\Queue as CliQueue;

/**
 * @internal
 *
 * @coversNothing
 */
class MailQueueTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // tests
    public function testPushMailJosbToQueue(): void
    {
        $qs = new QueueStub();
        \Yii::$container->setSingletons([
            CliQueue::class => fn () => $qs,
        ]);
        $result = \Yii::$app->createControllerByID('mail')->run('push');
        verify($result)->equals(ExitCode::OK);

        verify($qs->msgs)->arrayCount(2);
    }

    public function testListen(): void
    {
        $result = \Yii::$app->runAction('queue/listen');
        verify($result)->equals(ExitCode::OK);
    }

    protected function _before(): void
    {
    }

    protected function _after(): void
    {
    }
}

class QueueStub extends CliQueue
{
    public array $msgs = [];

    public function status($id): int
    {
        return CliQueue::STATUS_DONE;
    }

    protected function pushMessage($message, $ttr, $delay, $priority): string
    {
        return array_push($this->msgs, $message) - 1;
    }
}
