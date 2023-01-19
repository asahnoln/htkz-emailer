<?php

namespace tests\unit\commands;

use app\commands\MailController;
use app\services\emailer\db\DbAudience;
use app\services\emailer\db\DbOffer;
use app\services\emailer\db\DbQueueStore;
use app\services\emailer\Emailer;
use tests\unit\services\emailer\AnalyticsStub;
use tests\unit\services\emailer\AudienceStub;
use tests\unit\services\emailer\MailerSpy;
use tests\unit\services\emailer\OfferStub;
use tests\unit\services\emailer\QueueStoreStub;
use Yii;
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
class SendMailTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // tests
    public function testSendMailCommand(): void
    {
        // Static check for function in the controller
        // $result = Yii::$app->createControllerByID('mail')->run('send');
        // verify($result)->equals(ExitCode::OK);

        $this->createCities();

        $mc = new MailController('', '');
        $m = new MailerSpy();
        $a = new AnalyticsStub();

        $e = new Emailer($m, $a);
        $msg = new Message();
        $q = new QueueStoreStub();
        $q->data = [];
        $o = new OfferStub();
        $a = new AudienceStub();

        $code = $mc->actionSend($e, $msg, $q, $a, $o);

        verify($code)->isInt();
        verify($code)->equals(ExitCode::OK);

        verify($m->sentMessages)->arrayCount(6);
    }

    public function testDbQueue(): void
    {
        $this->createMails();
        $this->createOffers();

        // TODO: Move duplicated helper somewhere else
        $client = $this->make(Client::class, [
            'get' => function () {
                return $this->make(Request::class, [
                    'send' => function () {
                        return $this->make(Response::class, [
                            'data' => [
                                'tours' => [
                                    [
                                        'hotel' => ['name' => 'good'],
                                        'price' => ['forTour' => 1],
                                    ],
                                    [
                                        'hotel' => ['name' => 'bad'],
                                        'price' => ['forTour' => 10],
                                    ],
                                    [
                                        'hotel' => ['name' => 'ugly'],
                                        'price' => ['forTour' => 100],
                                    ],
                                ],
                            ],
                            'getStatusCode' => 200,
                        ]);
                    },
                ]);
            },
        ]);

        $mc = new MailController('', '');
        $m = new MailerSpy();
        $a = new AnalyticsStub();

        $e = new Emailer($m, $a);
        $msg = new Message();
        $q = new DbQueueStore();
        $o = new DbOffer($client, 'http://testurl.com', 'secret');
        $a = new DbAudience();

        $code = $mc->actionSend($e, $msg, $q, $a, $o);

        $mm = \Yii::$app->db->createCommand('SELECT * FROM {{%mail_message}}')->queryAll();
        verify($m->sentMessages)->arrayCount(5);
        verify($mm[0]['state'])->equals(2);
    }

    public function testDbFailingQueue(): void
    {
        $this->createMails();
        $this->createOffers();

        // TODO: Move duplicated helper somewhere else
        $client = $this->make(Client::class, [
            'get' => function () {
                return $this->make(Request::class, [
                    'send' => function () {
                        return $this->make(Response::class, [
                            'data' => [
                                'tours' => [
                                    [
                                        'hotel' => ['name' => 'good'],
                                        'price' => ['forTour' => 1],
                                    ],
                                    [
                                        'hotel' => ['name' => 'bad'],
                                        'price' => ['forTour' => 10],
                                    ],
                                    [
                                        'hotel' => ['name' => 'ugly'],
                                        'price' => ['forTour' => 100],
                                    ],
                                ],
                            ],
                            'getStatusCode' => 200,
                        ]);
                    },
                ]);
            },
        ]);

        $mc = new MailController('', '');
        $m = new MailerSpy(failing: true);
        $a = new AnalyticsStub();

        $e = new Emailer($m, $a);
        $msg = new Message();
        $q = new DbQueueStore();
        $o = new DbOffer($client, 'http://testurl.com', 'secret');
        $a = new DbAudience();

        $code = $mc->actionSend($e, $msg, $q, $a, $o);

        $mm = \Yii::$app->db->createCommand('SELECT * FROM {{%mail_message}}')->queryAll();
        verify($mm[0]['state'])->equals(3);
        verify($mm[0]['error_count'])->equals(1);
    }

    // TODO: Remove duplication
    protected function createMails(): void
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
                    [4, 'd@a.a', 2, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'], // Wrong city
                    [5, 'e@a.a', 1, 0, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'], // is not active
                    [6, 'f@a.a', 1, 1, 1, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'], // is deleted
                    [7, 'g@a.a', 1, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'], // mail was sent during the week
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

    protected function createCities(): void
    {
        \Yii::$app->db
            ->createCommand('DELETE FROM {{%city}} WHERE id > 3')
            ->execute()
        ;

        // Yii::$app->db
        //     ->createCommand()
        //     ->batchInsert(
        //         '{{%city}}',
        //         ['id'],
        //         ['1'],
        //         ['2'],
        //         ['3'],
        //     );
    }

    // TODO: Remove duplication
    protected function createOffers(): void
    {
        \Yii::$app->db
            ->createCommand()
            ->batchInsert(
                '{{%post_original}}',
                ['id', 'brand_id', 'title', 'info', 'price_for_tour', 'cur', 'endDate', 'priority', 'city_id', 'hidden_from_site', 'url', 'title_uk', 'info_uk', 'content', 'content_uk', 'seo_bottom_text', 'price', 'old_price', 'discount', 'file', 'type', 'inc_fly', 'inc_transfer', 'inc_insurance', 'inc_hotel', 'inc_visa', 'addPay', 'addDate', 'changeDate', 'hideDaysToEnd', 'is_advertising', 'views', 'noindex', 'staticUrl', 'showSite', 'autoUpdate', 'autoDateFrom', 'autoDateTo', 'autoUpdateDate', 'autoCombi', 'autoNights', 'autoMeal', 'autoStars', 'auto_operators', 'noChangeTitle', 'noChangePhoto', 'noChangeDiscount', 'noChangeEndDate', 'hotels', 'author_id', 'hidden_from_admin'],
                [
                    [1, 1, 'test offer 1', 'test offer info 1', '', 'T', date('Y-m-d H:i:s', strtotime('next year')), 10, 2, 0, 'test url 1', 'titleuk', 'infouk', 'content', 'contentuk', 'seo test', '', '', 'test discount', '', 'test type', 'test fly', 'transfer test', 'ins test', 'hot test', 'visa test', 'ptest', '1971-02-02 01:01:01', '1971-02-02 01:01:01', 'test', 'advtest', 'vietest', 'noindex_test', 'urlTest', 'showTest', 'autoTest', '1971-02-02 01:01:01', '1971-02-02 01:01:01', '1971-02-02 01:01:01', 'combiTest', 'nightsTest', 'mealTest', 'autoStars', 'optest', 'chTest', 'chTest', 'chTest', 'ch', 'chTest', 'chTest', 'chTest'],
                    // Sort priority
                    [2, 1, 'test offer 2', 'test offer info 2', '', 'T', date('Y-m-d H:i:s', strtotime('next year')), 20, 2, 0, 'test url 2', 'titleuk', 'infouk', 'content', 'contentuk', 'seo test', '', '', 'test discount', '', 'test type', 'test fly', 'transfer test', 'ins test', 'hot test', 'visa test', 'ptest', '1971-02-02 01:01:01', '1971-02-02 01:01:01', 'test', 'advtest', 'vietest', 'noindex_test', 'urlTest', 'showTest', 'autoTest', '1971-02-02 01:01:01', '1971-02-02 01:01:01', '1971-02-02 01:01:01', 'combiTest', 'nightsTest', 'mealTest', 'autoStars', 'optest', 'chTest', 'chTest', 'chTest', 'ch', 'chTest', 'chTest', 'chTest'],
                    // Filter endDate
                    [3, 1, 'test offer 3', 'test offer info 3', '', 'T', date('Y-m-d H:i:s', strtotime('yesterday')), 30, 2, 0, 'test url 3', 'titleuk', 'infouk', 'content', 'contentuk', 'seo test', '', '', 'test discount', '', 'test type', 'test fly', 'transfer test', 'ins test', 'hot test', 'visa test', 'ptest', '1971-03-03 01:01:01', '1971-03-03 01:01:01', 'test', 'advtest', 'vietest', 'noindex_test', 'urlTest', 'showTest', 'autoTest', '1971-03-03 01:01:01', '1971-03-03 01:01:01', '1971-03-03 01:01:01', 'combiTest', 'nightsTest', 'mealTest', 'autoStars', 'optest', 'chTest', 'chTest', 'chTest', 'ch', 'chTest', 'chTest', 'chTest'],
                    // Filter hidden from site
                    [4, 1, 'test offer 4', 'test offer info 4', '', 'T', date('Y-m-d H:i:s', strtotime('next year')), 40, 2, 1, 'test url 4', 'titleuk', 'infouk', 'content', 'contentuk', 'seo test', '', '', 'test discount', '', 'test type', 'test fly', 'transfer test', 'ins test', 'hot test', 'visa test', 'ptest', '1971-04-04 01:01:01', '1971-04-04 01:01:01', 'test', 'advtest', 'vietest', 'noindex_test', 'urlTest', 'showTest', 'autoTest', '1971-04-04 01:01:01', '1971-04-04 01:01:01', '1971-04-04 01:01:01', 'combiTest', 'nightsTest', 'mealTest', 'autoStars', 'optest', 'chTest', 'chTest', 'chTest', 'ch', 'chTest', 'chTest', 'chTest'],
                    // Filter city
                    [5, 1, 'test offer 5', 'test offer info 5', '', 'T', date('Y-m-d H:i:s', strtotime('next year')), 50, 1, 0, 'test url 5', 'titleuk', 'infouk', 'content', 'contentuk', 'seo test', '', '', 'test discount', '', 'test type', 'test fly', 'transfer test', 'ins test', 'hot test', 'visa test', 'ptest', '1971-05-05 01:01:01', '1971-05-05 01:01:01', 'test', 'advtest', 'vietest', 'noindex_test', 'urlTest', 'showTest', 'autoTest', '1971-05-05 01:01:01', '1971-05-05 01:01:01', '1971-05-05 01:01:01', 'combiTest', 'nightsTest', 'mealTest', 'autoStars', 'optest', 'chTest', 'chTest', 'chTest', 'ch', 'chTest', 'chTest', 'chTest'],
                ]
            )
            ->execute()
        ;
    }
}
