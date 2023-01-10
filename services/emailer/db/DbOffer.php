<?php

namespace app\services\emailer\db;

use app\services\emailer\OfferMessage;
use app\services\emailer\interfaces\OfferInterface;
use yii\db\Query;

class DbOffer implements OfferInterface
{
    public function find(string $city): OfferMessage
    {
        $offer = (new Query())
            ->select(['title', 'priority'])
            ->from('tbl_post_original')
            ->where(['city_id' => $city, 'hidden_from_site' => 0])
            ->andWhere(['>', 'endDate', strftime('%F %T')])
            ->orderBy(['priority' => SORT_DESC])
            ->one();


        return (new OfferMessage($offer['title'], 'content'));
    }
}
