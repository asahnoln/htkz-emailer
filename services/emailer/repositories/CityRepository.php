<?php

namespace app\services\emailer\repositories;

/**
 * Города в БД.
 */
class CityRepository
{
    /**
     * Получить все города из БД.
     *
     * @return array<string,any> Массив городов
     */
    public function findAll(): array
    {
        return \Yii::$app->db->createCommand('SELECT * FROM {{%city}}')->queryAll();
    }
}
