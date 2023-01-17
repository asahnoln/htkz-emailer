<?php

namespace services\emailer;

use app\services\emailer\db\DbQueueStore;
use app\services\emailer\interfaces\QueueStoreInterface;
use app\services\emailer\QueueMessage;

/**
 * @internal
 *
 * @coversNothing
 */
class DbQueueStoreTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    public function createMails(): void
    {
        \Yii::$app->db
            ->createCommand()
            ->batchInsert(
                'tbl_mail',
                ['id', 'email', 'city', 'active', 'del', 'site', 'place', 'type', 'addDate', 'activationSendDate', 'activationReadDate', 'reactivationSendDate', 'reactivationReadDate', 'del_type', 'delDate', 'restoreDate'],
                [
                    [1, 'a@a.a', 1, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'],
                    [2, 'b@b.b', 1, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'],
                ]
            )
            ->execute()
        ;
    }

    // tests
    public function testQueueSaves(): void
    {
        $qs = new DbQueueStore();
        verify($qs)->instanceOf(QueueStoreInterface::class);

        $qm = new QueueMessage('1', 'a@a.a', 'Wow Offer', 'Offer Content');
        $qs->send($qm);
        $qm = new QueueMessage('2', 'test2@mail.com', 'Wow Offer', 'Offer Content');
        $qs->send($qm);

        $msgs = \Yii::$app->db->createCommand('SELECT * FROM tbl_mail_message')->queryAll();
        verify($msgs)->arrayCount(2);
        verify($msgs[0]['title'])->equals('Wow Offer');
        verify($msgs[0]['content'])->equals('Offer Content');
        verify($msgs[0]['site'])->equals(1);
        verify($msgs[0]['state'])->equals(0);
        verify($msgs[0]['is_sending'])->equals(0);
        verify($msgs[0]['chunk_sending_started_at'])->equals('1970-01-01 00:00:00');
        verify($msgs[0]['send_count'])->equals(0);
        verify($msgs[0]['error_count'])->equals(0);
        verify($msgs[0]['read_count'])->equals(0);
        verify($msgs[0]['site_visit_count'])->equals(0);
        verify($msgs[0]['previewEmail'])->equals('');
        verify($msgs[0]['addDate'])->equals(strftime('%F %T'));
        verify($msgs[0]['activationDate'])->equals('1970-01-01 00:00:00');
        verify($msgs[0]['startDate'])->equals(strftime('%F %T'));
        verify($msgs[0]['endDate'])->equals(strftime('%F %T'));
        verify($msgs[0]['custom_file'])->equals('');
        verify($msgs[0]['activationToken'])->equals('');

        // TODO: site -> 1? hottour
        // TODO: Confirm all of the above values
    }

    public function testQueueReceives(): void
    {
        $qs = new DbQueueStore();
        verify($qs)->instanceOf(QueueStoreInterface::class);

        $qm1 = new QueueMessage('1', 'a@a.a', 'Wow Offer', 'Offer Content');
        $qs->send($qm1);
        $qm2 = new QueueMessage('2', 'b@b.b', 'Wow Offer 2', 'Offer Content 2');
        $qs->send($qm2);

        $received = $qs->receive();
        verify($received)->equals($qm2);

        $received = $qs->receive();
        verify($received)->equals($qm1);

        verify($qs->receive())->null();
    }

    protected function _before(): void
    {
        $this->createMails();
    }

    protected function _after(): void
    {
    }
}
