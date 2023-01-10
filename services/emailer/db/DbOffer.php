<?php

namespace app\services\emailer\db;

use app\services\emailer\interfaces\OfferInterface;
use yii\db\Query;
use yii\mail\MessageInterface;
use yii\symfonymailer\Message;

class DbOffer implements OfferInterface
{
    public function findAndCompose(string $city): MessageInterface
    {
        $offer = (new Query())
            ->select(['title', 'priority'])
            ->from('tbl_post_original')
            ->where(['city_id' => $city, 'hidden_from_site' => 0])
            ->andWhere(['>', 'endDate', strftime('%F %T')])
            ->orderBy(['priority' => SORT_DESC])
            ->one();

        $m = new Message();
        $m->setSubject($offer['title']);

        return $m;
    }
}
