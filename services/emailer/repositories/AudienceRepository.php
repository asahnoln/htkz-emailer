<?php

namespace app\services\emailer\repositories;

use app\services\emailer\interfaces\AudienceInterface;
use app\services\emailer\Subscriber;
use yii\db\Query;

/**
 * Аудитория, хранимая в БД.
 */
class AudienceRepository implements AudienceInterface
{
    /**
     * {@inheritdoc}
     */
    public function findAll(string $city): array
    {
        $items = (new Query())
            ->select(['m.id', 'm.email', 'mm.endDate'])
            ->from('{{%mail}} m')
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
