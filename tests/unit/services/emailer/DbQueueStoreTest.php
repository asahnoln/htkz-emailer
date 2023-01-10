<?php

namespace services\emailer;

use app\services\emailer\interfaces\QueueStoreInterface;

class DbQueueStoreTest extends \Codeception\Test\Unit
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
    public function testQueueSaves(): void
    {
        $qs = new DbQueueStore();

        verify($qs)->instanceOf(QueueStoreInterface::class);
    }
}
