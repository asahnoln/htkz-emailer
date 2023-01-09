<?php

namespace services;

use app\services\AnalyticsInterface;
use app\services\Emailer;
use yii\mail\MailerInterface;
use yii\mail\MessageInterface;

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
        // Yii::$app->db
        //    ->createCommand()
        //    ->insert('city', [
        //        'id' => 1,
        //        'name' => 'Алматы2',
        //        'nameFrom' => 'Алматы2',
        //        'sort' => 10,
        //    ])
        //    ->execute();

        $mailer = new MailerStub();
        $analytics = new AnalyticsStub();

        $e = new Emailer($mailer, $analytics);
        $result = $e->send('arthur@example.com');

        verify($result)->true();
        verify($mailer->message->getTo())->arrayHasKey('arthur@example.com');
    }
}

class MailerStub implements MailerInterface
{
    public MessageInterface $message;

    public function sent(): bool
    {
        return $this->_sent;
    }

    public function compose($view = null, array $params = []): void
    {
    }

    public function send($message): void
    {
        $this->message = $message;
    }

    public function sendMultiple(array $messages): void
    {
    }
}

class AnalyticsStub implements AnalyticsInterface
{
    public function send(): bool
    {
        return true;
    }
}
