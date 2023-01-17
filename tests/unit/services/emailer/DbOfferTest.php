<?php

namespace tests\unit\services\emailer;

use app\services\emailer\db\DbOffer;
use app\services\emailer\interfaces\OfferInterface;
use Codeception\Stub\Expected;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\httpclient\Response;

/**
 * @internal
 *
 * @coversNothing
 */
class DbOfferTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    // tests
    public function testFindsOfferAndComposesMessage(): void
    {
        $this->createOffers();

        // TODO: How to create list of tours? What data to use?
        $url = 'http://testapi.com/somwhere';
        $client = $this->make(Client::class, [
            'get' => Expected::once(function ($u, $data/* , $headers */) use ($url) {
                verify($u)->equals($url);
                verify($data['access-token'])->equals('secretToken');
                verify($data['id'])->equals(2);

                return $this->make(Request::class, [
                    'send' => Expected::once(function () {
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
                        ]);
                    }),
                ]);
            }),
        ]);

        $o = new DbOffer($client, $url, 'secretToken');

        $result = $o->find('2');

        verify($o)->instanceOf(OfferInterface::class);
        verify($result->title)->equals('test offer 2');
        verify($result->content)->equals("good - 1\nbad - 10\nugly - 100");
    }

    public function testNoOfferFound(): void
    {
        $o = new DbOffer($this->make(Client::class), 'test', 'token');
        $result = $o->find('99999');
        verify($result)->null();
    }

    protected function _before(): void
    {
    }

    protected function _after(): void
    {
    }

    protected function createOffers(): void
    {
        \Yii::$app->db
            ->createCommand()
            ->batchInsert(
                'tbl_post_original',
                ['id', 'brand_id', 'title', 'info', 'price_for_tour', 'cur', 'endDate', 'priority', 'city_id', 'hidden_from_site', 'url', 'title_uk', 'info_uk', 'content', 'content_uk', 'seo_bottom_text', 'price', 'old_price', 'discount', 'file', 'type', 'inc_fly', 'inc_transfer', 'inc_insurance', 'inc_hotel', 'inc_visa', 'addPay', 'addDate', 'changeDate', 'hideDaysToEnd', 'is_advertising', 'views', 'noindex', 'staticUrl', 'showSite', 'autoUpdate', 'autoDateFrom', 'autoDateTo', 'autoUpdateDate', 'autoCombi', 'autoNights', 'autoMeal', 'autoStars', 'auto_operators', 'noChangeTitle', 'noChangePhoto', 'noChangeDiscount', 'noChangeEndDate', 'hotels', 'author_id', 'hidden_from_admin'],
                [
                    [1, 1, 'test offer 1', 'test offer info 1', '', 'T', strftime('%F %T', strtotime('next year')), 10, 2, 0, 'test url 1', 'titleuk', 'infouk', 'content', 'contentuk', 'seo test', '', '', 'test discount', '', 'test type', 'test fly', 'transfer test', 'ins test', 'hot test', 'visa test', 'ptest', '1971-02-02 01:01:01', '1971-02-02 01:01:01', 'test', 'advtest', 'vietest', 'noindex_test', 'urlTest', 'showTest', 'autoTest', '1971-02-02 01:01:01', '1971-02-02 01:01:01', '1971-02-02 01:01:01', 'combiTest', 'nightsTest', 'mealTest', 'autoStars', 'optest', 'chTest', 'chTest', 'chTest', 'ch', 'chTest', 'chTest', 'chTest'],
                    // Sort priority
                    [2, 1, 'test offer 2', 'test offer info 2', '', 'T', strftime('%F %T', strtotime('next year')), 20, 2, 0, 'test url 2', 'titleuk', 'infouk', 'content', 'contentuk', 'seo test', '', '', 'test discount', '', 'test type', 'test fly', 'transfer test', 'ins test', 'hot test', 'visa test', 'ptest', '1971-02-02 01:01:01', '1971-02-02 01:01:01', 'test', 'advtest', 'vietest', 'noindex_test', 'urlTest', 'showTest', 'autoTest', '1971-02-02 01:01:01', '1971-02-02 01:01:01', '1971-02-02 01:01:01', 'combiTest', 'nightsTest', 'mealTest', 'autoStars', 'optest', 'chTest', 'chTest', 'chTest', 'ch', 'chTest', 'chTest', 'chTest'],
                    // Filter endDate
                    [3, 1, 'test offer 3', 'test offer info 3', '', 'T', strftime('%F %T', strtotime('1 minute ago')), 30, 2, 0, 'test url 3', 'titleuk', 'infouk', 'content', 'contentuk', 'seo test', '', '', 'test discount', '', 'test type', 'test fly', 'transfer test', 'ins test', 'hot test', 'visa test', 'ptest', '1971-03-03 01:01:01', '1971-03-03 01:01:01', 'test', 'advtest', 'vietest', 'noindex_test', 'urlTest', 'showTest', 'autoTest', '1971-03-03 01:01:01', '1971-03-03 01:01:01', '1971-03-03 01:01:01', 'combiTest', 'nightsTest', 'mealTest', 'autoStars', 'optest', 'chTest', 'chTest', 'chTest', 'ch', 'chTest', 'chTest', 'chTest'],
                    // Filter hidden from site
                    [4, 1, 'test offer 4', 'test offer info 4', '', 'T', strftime('%F %T', strtotime('next year')), 40, 2, 1, 'test url 4', 'titleuk', 'infouk', 'content', 'contentuk', 'seo test', '', '', 'test discount', '', 'test type', 'test fly', 'transfer test', 'ins test', 'hot test', 'visa test', 'ptest', '1971-04-04 01:01:01', '1971-04-04 01:01:01', 'test', 'advtest', 'vietest', 'noindex_test', 'urlTest', 'showTest', 'autoTest', '1971-04-04 01:01:01', '1971-04-04 01:01:01', '1971-04-04 01:01:01', 'combiTest', 'nightsTest', 'mealTest', 'autoStars', 'optest', 'chTest', 'chTest', 'chTest', 'ch', 'chTest', 'chTest', 'chTest'],
                    // Filter city
                    [5, 1, 'test offer 5', 'test offer info 5', '', 'T', strftime('%F %T', strtotime('next year')), 50, 1, 0, 'test url 5', 'titleuk', 'infouk', 'content', 'contentuk', 'seo test', '', '', 'test discount', '', 'test type', 'test fly', 'transfer test', 'ins test', 'hot test', 'visa test', 'ptest', '1971-05-05 01:01:01', '1971-05-05 01:01:01', 'test', 'advtest', 'vietest', 'noindex_test', 'urlTest', 'showTest', 'autoTest', '1971-05-05 01:01:01', '1971-05-05 01:01:01', '1971-05-05 01:01:01', 'combiTest', 'nightsTest', 'mealTest', 'autoStars', 'optest', 'chTest', 'chTest', 'chTest', 'ch', 'chTest', 'chTest', 'chTest'],
                ]
            )
            ->execute()
        ;
    }
}
