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
        $msgs = \Yii::$app->db->createCommand('SELECT * FROM {{%mail_message}}')->queryAll();

        $m = new MailerSpy();
        $a = new \AnalyticsStub();
        $e = new Emailer($m, $a);
        $s = new SubscriberEntity('test@mail.com', 4);
        $o = new OfferEntity('Great Thing', 'Great Body');
        $mj = new MailJob($e, $s, $o);

        $q = new \QueueStub();
        $mj->execute($q);

        verify($m->sentMessages)->arrayCount(1);
        verify($m->sentMessages[0]->getTo())->arrayHasKey('test@mail.com');
        verify($m->sentMessages[0]->getSubject())->equals('Great Thing');
        verify($m->sentMessages[0]->getTextBody())->equals('Great Body');
        verify($a->ids[0])->equals('4');

        $msgs = \Yii::$app->db->createCommand('SELECT * FROM {{%mail_message}}')->queryAll();
        verify($msgs[1]['state'])->equals(2);
        verify($msgs[1]['send_count'])->equals(1);

        // Shouldn't touch the old one
        verify($msgs[0]['state'])->equals(1);
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
                ['mail_id', 'title', 'titleBig', 'content', 'site', 'state', 'is_sending', 'chunk_sending_started_at', 'send_count', 'error_count', 'read_count', 'site_visit_count', 'previewEmail', 'addDate', 'activationDate', 'startDate', 'endDate', 'custom_file', 'activationToken',
                ],
                [
                    [4, 'mail test 2', 'mail test 2', 'mail content 2', 1, 1, 0, '1970-01-01 00:00:00', 0, 1, 0, 0, '', date('Y-m-d H:i:s'), '1970-01-01 00:00:00', date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('8 days ago')), '', ''],
                    [4, 'mail test', 'mail test', 'mail content', 1, 0, 0, '1970-01-01 00:00:00', 0, 0, 0, 0, '', date('Y-m-d H:i:s'), '1970-01-01 00:00:00', date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('8 days ago')), '', ''],
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
