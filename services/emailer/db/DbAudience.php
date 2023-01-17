<?php

namespace app\services\emailer\db;

use app\services\emailer\interfaces\AudienceInterface;
use app\services\emailer\Subscriber;
use yii\db\Query;

class DbAudience implements AudienceInterface
{
    /**
     * @return Subscriber[]
     */
    public function findAll(string $city): array
    {
        $items = (new Query())
            ->select(['m.id', 'm.email', 'mm.endDate'])
            ->from('tbl_mail m')
            ->leftJoin('{{%mail_message}} mm', 'm.id = mm.mail_id')
            ->where(['city' => $city, 'active' => 1, 'del' => 0])
            ->all()
        ;

        $subs = [];
        foreach ($items as $item) {
            if (strtotime($item['endDate'] ?? '') > strtotime('7 days ago')) {
                continue;
            }

            $subs[] = new Subscriber($item['email'], $item['id']);
        }

        return $subs;
    }
}
