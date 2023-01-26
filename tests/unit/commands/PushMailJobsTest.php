<?php

namespace commands;

use app\commands\MailController;
use app\services\emailer\db\DbAudience;
use app\services\emailer\db\DbOffer;
use app\services\emailer\db\DbQueueStore;
use app\services\emailer\Emailer;
use tests\unit\services\emailer\AnalyticsStub;
use tests\unit\services\emailer\MailerSpy;
use yii\console\ExitCode;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\httpclient\Response;
use yii\symfonymailer\Message;

/**
 * @internal
 *
 * @coversNothing
 */
class PushMailJobsTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // tests
    public function testPushMailJosbToQueue(): void
    {
        // TODO: Fix config for this test (should use console.php), then we can test it simpler?
        $result = \Yii::$app->createControllerByID('mail')->run('push');
        verify($result)->equals(ExitCode::OK);
        // $client = $this->make(Client::class, [
        //     'get' => function () {
        //         return $this->make(Request::class, [
        //             'send' => function () {
        //                 return $this->make(Response::class, [
        //                     'data' => [
        //                         'tours' => [
        //                             [
        //                                 'hotel' => ['name' => 'good'],
        //                                 'price' => ['forTour' => 1],
        //                             ],
        //                             [
        //                                 'hotel' => ['name' => 'bad'],
        //                                 'price' => ['forTour' => 10],
        //                             ],
        //                             [
        //                                 'hotel' => ['name' => 'ugly'],
        //                                 'price' => ['forTour' => 100],
        //                             ],
        //                         ],
        //                     ],
        //                     'getStatusCode' => 200,
        //                 ]);
        //             },
        //         ]);
        //     },
        // ]);
        // $mc = new MailController('', '');
        // $m = new MailerSpy();
        // $a = new AnalyticsStub();
        //
        // $e = new Emailer($m, $a);
        // $msg = new Message();
        // $q = new DbQueueStore();
        // $o = new DbOffer($client, 'http://testurl.com', 'secret');
        // $a = new DbAudience();
        //
        // $code = $mc->actionPush();
        //
        // verify($code)->isInt();
        // verify($code)->equals(ExitCode::OK);

        // verify($m->sentMessages)->arrayCount(6);
    }

    protected function _before(): void
    {
    }

    protected function _after(): void
    {
    }
}
