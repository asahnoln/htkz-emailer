<?php

namespace app\services\emailer;

use app\services\emailer\interfaces\AnalyticsInterface;
use yii\httpclient\Client;

/**
 * Сервис аналитики Amplitude.
 */
class Amplitude implements AnalyticsInterface
{
    /**
     * @param Client $client HTTP-клиент для запросов к Amplitude
     * @param string $url    Ссылка на endpoint в Amplitude
     * @param string $key    Токен для запросов в Amplitude
     */
    public function __construct(private Client $client, private string $url, private string $key)
    {
    }

    /**
     * {@inheritdoc}
     */
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
