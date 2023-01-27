<?php

namespace app\services\emailer\db;

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
