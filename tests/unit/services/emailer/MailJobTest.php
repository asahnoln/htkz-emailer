<?php

namespace tests\unit\services\emailer;

use app\services\emailer\Emailer;
use app\services\emailer\entities\OfferEntity;
use app\services\emailer\entities\SubscriberEntity;
use app\services\emailer\jobs\MailJob;

/**
 * @internal
 *
 * @coversNothing
 */
class MailJobTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // tests
    public function testSendsMail(): void
    {
        $this->createMails();

        $mailSpy = new MailerSpy();
        $analytics = new \AnalyticsStub();
        $emailer = new Emailer($mailSpy, $analytics);
        $sub = new SubscriberEntity('test@mail.com', 4);
        $off = new OfferEntity('Great Thing', [
            ['name' => 'good', 'price' => 10],
            ['name' => 'bad', 'price' => 20],
            ['name' => 'ugly', 'price' => 100],
        ]);

        \Yii::$container->setSingletons([
            Emailer::class => fn () => $emailer,
        ]);
        $mj = new MailJob(11, $sub->email, $sub->id, $off->title, $off->payload);

        $mj->execute(new \QueueStub());

        verify($mailSpy->sentMessages)->arrayCount(1);
        verify($mailSpy->sentMessages[0]->getTo())->arrayHasKey('test@mail.com');
        verify($mailSpy->sentMessages[0]->getSubject())->equals('Great Thing');
        verify($mailSpy->sentMessages[0]->getTextBody())->equals("good - 10\nbad - 20\nugly - 100");
        verify($analytics->ids[0])->equals('4');

        $msgs = \Yii::$app->db->createCommand('SELECT * FROM {{%mail_message}}')->queryAll();
        verify($msgs[1]['send_count'])->equals(6);

        // Shouldn't touch the old one
        verify($msgs[0]['send_count'])->equals(0);
    }

    public function testMustNotSendFinishedMailMessage(): void
    {
        $this->createMails();

        $mailSpy = new MailerSpy();
        $analytics = new \AnalyticsStub();
        $emailer = new Emailer($mailSpy, $analytics);
        $sub = new SubscriberEntity('test@mail.com', 4);
        $off = new OfferEntity('Great Thing', [
            ['name' => 'good', 'price' => 10],
            ['name' => 'bad', 'price' => 20],
            ['name' => 'ugly', 'price' => 100],
        ]);

        \Yii::$container->setSingletons([
            Emailer::class => fn () => $emailer,
        ]);
        $mj = new MailJob(12, 'test@test.com', 1, 'title', []);
        $mj->execute(new \QueueStub());

        verify($mailSpy->sentMessages)->arrayCount(0);

        $msgs = \Yii::$app->db->createCommand('SELECT * FROM {{%mail_message}}')->queryAll();
        verify($msgs[2]['send_count'])->equals(0);
    }

    protected function createMails(): void
    {
        \Yii::$app->db
            ->createCommand()
            ->batchInsert(
                '{{%mail}}',
                ['id', 'email', 'city', 'active', 'del', 'site', 'place', 'type', 'addDate', 'activationSendDate', 'activationReadDate', 'reactivationSendDate', 'reactivationReadDate', 'del_type', 'delDate', 'restoreDate'],
                [
                    [4, 'a@a.a', 1, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'],
                ]
            )
            ->execute()
        ;

        \Yii::$app->db->createCommand()
            ->batchInsert(
                '{{%mail_message}}',
                ['id', 'title', 'titleBig', 'content', 'site', 'state', 'is_sending', 'chunk_sending_started_at', 'send_count', 'error_count', 'read_count', 'site_visit_count', 'previewEmail', 'addDate', 'activationDate', 'startDate', 'endDate', 'custom_file', 'activationToken',
                ],
                [
                    [10, 'mail test 2', 'mail test 2', 'mail content 2', 1, 1, 0, '1970-01-01 00:00:00', 0, 1, 0, 0, '', date('Y-m-d H:i:s'), '1970-01-01 00:00:00', date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('8 days ago')), '', ''],
                    [11, 'mail test', 'mail test', 'mail content', 1, 1, 0, '1970-01-01 00:00:00', 5, 0, 0, 0, '', date('Y-m-d H:i:s'), '1970-01-01 00:00:00', date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('8 days ago')), '', ''],
                    [12, 'mail test', 'mail test', 'mail content', 1, 0, 0, '1970-01-01 00:00:00', 0, 0, 0, 0, '', date('Y-m-d H:i:s'), '1970-01-01 00:00:00', date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('8 days ago')), '', ''],
                ]
            )
            ->execute()
        ;
    }

    protected function _before(): void
    {
    }

    protected function _after(): void
    {
    }
}
