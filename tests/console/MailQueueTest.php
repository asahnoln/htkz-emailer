<?php

namespace commands;

use app\services\emailer\interfaces\OfferInterface;
use app\services\emailer\jobs\MailJob;
use app\services\emailer\repositories\OfferRepository;
use PHPUnit\Framework\MockObject\MockObject;
use yii\console\ExitCode;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\httpclient\Response;
use yii\mail\MailerInterface;
use yii\queue\cli\Queue as CliQueue;
use yii\symfonymailer\Mailer;

/**
 * @internal
 *
 * @coversNothing
 */
class MailQueueTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // tests
    public function testPushMailJosbToQueue(): void
    {
        $this->createMails();
        $this->createOffers();

        $qs = new \QueueStub();
        \Yii::$container->setSingletons([
            CliQueue::class => fn () => $qs,
        ]);
        \Yii::$container->setDefinitions([
            MailerInterface::class => [
                'class' => Mailer::class,
                'viewPath' => '@app/mail',
                // send all mails to a file by default.
                'useFileTransport' => true,
                'messageClass' => 'yii\symfonymailer\Message',
            ],
            OfferInterface::class => [
                'class' => OfferRepository::class,
                '__construct()' => [
                    $this->mockApi(),
                    $_ENV['API_URL'],
                    $_ENV['API_KEY'],
                ],
            ],
        ]);
        $result = \Yii::$app->createControllerByID('mail')->run('push');
        verify($result)->equals(ExitCode::OK);

        verify($qs->msgs)->arrayCount(4);
        verify(unserialize($qs->msgs[0]))->instanceOf(MailJob::class);

        $testDate = (new \DateTime())->format('Y-m-d H:i:s');
        $msgs = \Yii::$app->db->createCommand('SELECT * FROM {{%mail_message}}')->queryAll();
        verify($msgs)->arrayCount(7); // Already existing mail_messages plus new ones
        verify($msgs[3]['title'])->equals('test offer 5');
        verify($msgs[3]['content'])->stringContainsString('good');
        verify($msgs[3]['site'])->equals(1);
        verify($msgs[3]['state'])->equals(0);
        verify($msgs[3]['is_sending'])->equals(0);
        verify($msgs[3]['chunk_sending_started_at'])->equals('1970-01-01 00:00:00');
        verify($msgs[3]['send_count'])->equals(0);
        verify($msgs[3]['error_count'])->equals(0);
        verify($msgs[3]['read_count'])->equals(0);
        verify($msgs[3]['site_visit_count'])->equals(0);
        verify($msgs[3]['previewEmail'])->equals('');
        verify($msgs[3]['addDate'])->equals($testDate);
        verify($msgs[3]['activationDate'])->equals('1970-01-01 00:00:00');
        verify($msgs[3]['startDate'])->equals($testDate);
        verify($msgs[3]['endDate'])->equals($testDate);
        verify($msgs[3]['custom_file'])->equals('');
        verify($msgs[3]['activationToken'])->equals('');
    }

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
                    [4, 'd@a.a', 2, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'],
                    [5, 'e@a.a', 1, 0, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'], // is not active
                    [6, 'f@a.a', 1, 1, 1, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'], // is deleted
                    [7, 'g@a.a', 1, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'], // mail was sent during the week
                    [8, 'h@a.a', 22, 1, 0, 'test site', 'test place', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', '1970-01-01 00:00:00', 1, '1970-01-01 00:00:00', '1970-01-01 00:00:00'], // no offer for this city
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
                    [7, 'mail test', 'mail test', 'mail content', 1, 0, 0, '1970-01-01 00:00:00', 0, 0, 0, 0, '', date('Y-m-d H:i:s'), '1970-01-01 00:00:00', date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('5 days ago')), '', ''], // sent during the week, must be ignored
                    [1, 'mail test', 'mail test', 'mail content', 1, 0, 0, '1970-01-01 00:00:00', 0, 0, 0, 0, '', date('Y-m-d H:i:s'), '1970-01-01 00:00:00', date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('8 days ago')), '', ''], // sent more than week before, must be queried
                    [2, 'mail test', 'mail test', 'mail content', 1, 0, 0, '1970-01-01 00:00:00', 0, 0, 0, 0, '', date('Y-m-d H:i:s'), '1970-01-01 00:00:00', date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('7 days ago')), '', ''], // send right 1 week before, must be queried
                ]
            )
            ->execute()
        ;
    }

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
                    [5, 1, 'test offer 5', 'test offer info 5', '', 'T', date('Y-m-d H:i:s', strtotime('next year')), 50, 1, 0, 'test url 5', 'titleuk', 'infouk', 'content', 'contentuk', 'seo test', '', '', 'test discount', '', 'test type', 'test fly', 'transfer test', 'ins test', 'hot test', 'visa test', 'ptest', '1971-05-05 01:01:01', '1971-05-05 01:01:01', 'test', 'advtest', 'vietest', 'noindex_test', 'urlTest', 'showTest', 'autoTest', '1971-05-05 01:01:01', '1971-05-05 01:01:01', '1971-05-05 01:01:01', 'combiTest', 'nightsTest', 'mealTest', 'autoStars', 'optest', 'chTest', 'chTest', 'chTest', 'ch', 'chTest', 'chTest', 'chTest'],
                ]
            )
            ->execute()
        ;
    }

    protected function mockApi(): MockObject&Client
    {
        return $this->make(Client::class, [
            'get' => (function ($u, $data) {
                return $this->make(Request::class, [
                    'send' => (function () {
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
                    }),
                ]);
            }),
        ]);
    }

    protected function _before(): void
    {
    }

    protected function _after(): void
    {
    }
}
