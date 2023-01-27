<?php

namespace tests\unit\services\emailer;

use app\services\emailer\Emailer;
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
        $m = new MailerSpy();
        $a = new \AnalyticsStub();
        $e = new Emailer($m, $a);
        $mj = new MailJob($e, 'test@mail.com');

        $q = new \QueueStub();
        $mj->execute($q);

        verify($m->sentMessages)->arrayCount(1);
        verify($m->sentMessages[0]->getTo())->arrayHasKey('test@mail.com');
        verify($a->ids[0])->equals('4');
    }

    protected function _before(): void
    {
    }

    protected function _after(): void
    {
    }
}
