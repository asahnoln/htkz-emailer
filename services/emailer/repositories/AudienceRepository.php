<?php

namespace app\services\emailer\repositories;

use app\services\emailer\entities\SubscriberEntity;
use app\services\emailer\interfaces\AudienceInterface;
use yii\db\Query;

/**
 * Аудитория, хранимая в БД.
 */
class AudienceRepository implements AudienceInterface
{
    /**
     * {@inheritdoc}
     */
    public function findAll(int $city): array
    {
        $items = (new Query())
            ->select(['m.id', 'm.email'])
            ->from('{{%mail}} m')
            ->where(['city' => $city, 'active' => 1, 'del' => 0])
            ->all()
        ;

        $subs = [];
        foreach ($items as $item) {
            $subs[] = new SubscriberEntity($item['email'], $item['id']);
        }

        return $subs;
    }
}
