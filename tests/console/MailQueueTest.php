<?php

namespace commands;

use Yii;
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
        $this->createMails();

        $qs = new QueueStub();
        \Yii::$container->setSingletons([
            CliQueue::class => fn () => $qs,
        ]);
        $result = \Yii::$app->createControllerByID('mail')->run('push');
        verify($result)->equals(ExitCode::OK);

        verify($qs->msgs)->arrayCount(4);
    }

    public function createMails(): void
    {
        \Yii::$app->db
            ->createCommand()
            ->batchInsert(
                '{{%mail}}',
                ['id', 'email', 'city', 'active', 'del', 'site', 'place', 'type', 'addDate', 'activationSendDate', 'activationReadDate', 'reactivationSendDate', 'reactivationReadDate', 'del_type', 'delDate', 'restoreDate'],
                [
                    [1, 'a@a.a', 1, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'],
                    [2, 'b@a.a', 1, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'],
                    [3, 'c@a.a', 1, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'],
                    [4, 'd@a.a', 2, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'],
                    [5, 'e@a.a', 1, 0, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'], // is not active
                    [6, 'f@a.a', 1, 1, 1, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'], // is deleted
                    [7, 'g@a.a', 1, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'], // mail was sent during the week
                ]
            )
            ->execute()
        ;

        \Yii::$app->db->createCommand()
            ->batchInsert(
                '{{%mail_message}}',
                ['mail_id', 'title', 'titleBig', 'content', 'site', 'state', 'is_sending', 'chunk_sending_started_at', 'send_count', 'error_count', 'read_count', 'site_visit_count', 'previewEmail', 'addDate', 'activationDate', 'startDate', 'endDate', 'custom_file', 'activationToken',
                ],
                [
                    [7, 'mail test', 'mail test', 'mail content', 1, 0, 0, '1970-01-01 00:00:00', 0, 0, 0, 0, '', date('Y-m-d H:i:s'), '1970-01-01 00:00:00', date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('5 days ago')), '', ''], // sent during the week, must be ignored
                    [1, 'mail test', 'mail test', 'mail content', 1, 0, 0, '1970-01-01 00:00:00', 0, 0, 0, 0, '', date('Y-m-d H:i:s'), '1970-01-01 00:00:00', date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('8 days ago')), '', ''], // sent more than week before, must be queried
                    [2, 'mail test', 'mail test', 'mail content', 1, 0, 0, '1970-01-01 00:00:00', 0, 0, 0, 0, '', date('Y-m-d H:i:s'), '1970-01-01 00:00:00', date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('7 days ago')), '', ''], // send right 1 week before, must be queried
                ]
            )
            ->execute()
        ;
    }

    // public function testListen(): void
    // {
    //     $result = Yii::$app->runAction('queue/listen');
    //     verify($result)->equals(ExitCode::OK);
    // }

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
