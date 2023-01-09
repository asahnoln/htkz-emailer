<?php

namespace services;

use app\services\Emailer;
use Yii;

class EmailerTest extends \Codeception\Test\Unit
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
    public function testSendsEmails(): void
    {
        Yii::$app->db
           ->createCommand()
           ->insert('city', [
               'id' => 1,
               'name' => 'Алматы2',
               'nameFrom' => 'Алматы2',
               'sort' => 10,
           ])
           ->execute();

        $e = new Emailer();
        $result = $e->send();
        verify($result)->true();
    }
}
