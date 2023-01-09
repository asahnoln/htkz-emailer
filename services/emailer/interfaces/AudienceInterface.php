<?php
namespace app\services\emailer\interfaces;

interface AudienceInterface
{

    /**
     * @return app\services\emailer\Subscriber[]
     */
    public function findAll(string $city): array;
}
