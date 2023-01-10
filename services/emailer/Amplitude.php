<?php

namespace app\services\emailer;

use app\services\emailer\interfaces\AnalyticsInterface;
use yii\httpclient\Client;

class Amplitude implements AnalyticsInterface
{
    public function __construct(private Client $client, private string $url, private string $key)
    {
    }

    public function send(string $id): bool
    {
        $response = $this->client->post($this->url, [
            'api_key' => $this->key,
            'events' => [
                [
                    'event_type' => 'email send',
                    'device_id' => 'mail_'.$id,
                    'event_properties' => [
                        'type' => 'mailing',
                        'mail_type' => 'single',
                        'is_auto' => true,
                    ],
                ],
            ],
        ], [
            'content-type' => 'application/json',
            'accept' => '*/*',
        ])->send();

        return $response->isOk;
    }
}
