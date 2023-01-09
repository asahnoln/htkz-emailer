<?php

namespace services;

use Yii;
use app\services\emailer\db\DbOffer;
use app\services\emailer\interfaces\OfferInterface;

class DbOfferTest extends \Codeception\Test\Unit
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
    public function testFindsOfferAndComposesMessage(): void
    {
        Yii::$app->db
            ->createCommand()
        ->batchInsert('tbl_post_original',
            ['id', 'brand_id', 'title', 'info', 'price_for_tour', 'cur', 'endDate', 'priority', 'city_id', 'hidden_from_site', 'url', 'title_uk', 'info_uk', 'content', 'content_uk', 'seo_bottom_text', 'price', 'old_price', 'discount', 'file', 'type', 'inc_fly', 'inc_transfer', 'inc_insurance', 'inc_hotel', 'inc_visa', 'addPay', 'addDate', 'changeDate', 'hideDaysToEnd', 'is_advertising', 'views', 'noindex', 'staticUrl', 'showSite', 'autoUpdate', 'autoDateFrom', 'autoDateTo', 'autoUpdateDate', 'autoCombi', 'autoNights', 'autoMeal', 'autoStars', 'auto_operators', 'noChangeTitle', 'noChangePhoto', 'noChangeDiscount', 'noChangeEndDate', 'hotels', 'author_id', 'hidden_from_admin'], [
                [1, 1, 'test offer 1', 'test offer info 1', '', 'T', strftime('%Y-%m-%d %H:%M:%S', strtotime('next year')), 1, 2, 0, 'test url 1', 'titleuk', 'infouk', 'content', 'contentuk', 'seo test', '', '', 'test discount', '', 'test type', 'test fly', 'transfer test', 'ins test', 'hot test', 'visa test', 'ptest', '1971-02-02 01:01:01', '1971-02-02 01:01:01', 'test', 'advtest', 'vietest', 'noindex_test', 'urlTest', 'showTest', 'autoTest', '1971-02-02 01:01:01', '1971-02-02 01:01:01', '1971-02-02 01:01:01', 'combiTest', 'nightsTest', 'mealTest', 'autoStars', 'optest', 'chTest', 'chTest', 'chTest', 'ch', 'chTest', 'chTest', 'chTest'],
                [2, 1, 'test offer 2', 'test offer info 2', '', 'T', strftime('%Y-%m-%d %H:%M:%S', strtotime('next year')), 1, 2, 0, 'test url 2', 'titleuk', 'infouk', 'content', 'contentuk', 'seo test', '', '', 'test discount', '', 'test type', 'test fly', 'transfer test', 'ins test', 'hot test', 'visa test', 'ptest', '1971-02-02 01:01:01', '1971-02-02 01:01:01', 'test', 'advtest', 'vietest', 'noindex_test', 'urlTest', 'showTest', 'autoTest', '1971-02-02 01:01:01', '1971-02-02 01:01:01', '1971-02-02 01:01:01', 'combiTest', 'nightsTest', 'mealTest', 'autoStars', 'optest', 'chTest', 'chTest', 'chTest', 'ch', 'chTest', 'chTest', 'chTest'],
            ])
            ->execute();

        $a = new DbOffer();

        $result = $a->findAndCompose('2');

        verify($a)->instanceOf(OfferInterface::class);
        verify($result->getSubject())->equals('test offer 2');
    }
}
