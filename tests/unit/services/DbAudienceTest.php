<?php

namespace services;

use Yii;
use app\services\emailer\Subscriber;
use app\services\emailer\db\DbAudience;
use app\services\emailer\interfaces\AudienceInterface;

class DbAudienceTest extends \Codeception\Test\Unit
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
    public function testGetAudience(): void
    {
        Yii::$app->db
            ->createCommand()
            ->batchInsert(
                'tbl_mail',
                ['id', 'email', 'city', 'active', 'del', 'site', 'place', 'type', 'addDate', 'activationSendDate', 'activationReadDate', 'reactivationSendDate', 'reactivationReadDate', 'del_type', 'delDate', 'restoreDate'],
                [
                    [1, 'a@a.a', 1, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'],
                    [2, 'b@a.a', 1, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'],
                    [3, 'c@a.a', 1, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'],
                    [4, 'd@a.a', 2, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'], // Wrong city
                    // [4, 'd@a.a', 1, 0, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'], // TODO: Filter out not active?
                    // [4, 'd@a.a', 1, 0, 1, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'], // TODO: Filter out deleted?
                ]
            )
            ->execute();

        $a = new DbAudience();

        $result = $a->findAll('1');

        verify($a)->instanceOf(AudienceInterface::class);
        verify($result)->arrayCount(3);
        verify($result[0])->instanceOf(Subscriber::class);
        verify($result[0]->email)->equals('a@a.a');
        verify($result[0]->id)->equals(1);
    }
}
