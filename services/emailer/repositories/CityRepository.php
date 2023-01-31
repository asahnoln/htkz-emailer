<?php

namespace app\services\emailer\repositories;

/**
 * Аудитория, хранимая в БД.
 */
class CityRepository
{
    public function findAll(): array
    {
        return \Yii::$app->db->createCommand('SELECT * FROM {{%city}}')->queryAll();
    }
}
