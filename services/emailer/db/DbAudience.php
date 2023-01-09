<?php

namespace app\services\emailer\db;

use app\services\emailer\Subscriber;
use app\services\emailer\interfaces\AudienceInterface;
use yii\db\Query;

class DbAudience implements AudienceInterface
{
    /**
     * @return Subscriber[]
     */
    public function findAll(string $city): array
    {
        $items = (new Query())
            ->select(['id', 'email'])
            ->from('tbl_mail')
            ->where(['city' => $city])
            ->all();

        $subs = [];
        foreach ($items as $item) {
            $subs[] = new Subscriber($item['email'], $item['id']);
        }

        return $subs;
    }
}
