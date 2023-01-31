<?php

namespace app\services\emailer\repositories;

use app\services\emailer\interfaces\OfferInterface;
use app\services\emailer\OfferMessage;
use yii\db\Query;
use yii\httpclient\Client;

/**
 * Оффер, хранимый в БД и обращающийся к HT API за информацией о турах для составления текста письма.
 */
class OfferRepository implements OfferInterface
{
    /**
     * @param Client $client HTTP-клиент для запросов к HT API
     * @param string $url    Ссылка на endpoint в HT API
     * @param string $key    Токен для запросов в HT API
     */
    public function __construct(private Client $client, private string $url, private string $key)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $city): ?OfferMessage
    {
        $offer = $this->findOffer($city);
        if (!$offer) {
            \Yii::warning("Offer was not found in DB for city {$city}");

            return null;
        }

        $data = $this->apiRequest($offer['id']);
        if (!$data) {
            return null;
        }

        // TODO: Move out to a template
        $content = [];
        foreach ($data['tours'] as $tour) {
            $content[] = "{$tour['hotel']['name']} - {$tour['price']['forTour']}";
        }

        return new OfferMessage($offer['title'], implode("\n", $content));
    }

    /**
     * Найти оффер в БД по городу.
     *
     * @param string $city Город
     *
     * @return array|bool Массив с данными или false в отрицательном случае
     */
    protected function findOffer(string $city): array|bool
    {
        return (new Query())
            ->select(['id', 'title', 'priority'])
            ->from('{{%post_original}}')
            ->where(['city_id' => $city, 'hidden_from_site' => 0])
            ->andWhere(['>', 'endDate', date('Y-m-d H:i:s')])
            ->orderBy(['priority' => SORT_DESC])
            ->one()
        ;
    }

    /**
     * Получить информацию о турах по ID оффера.
     *
     * @param int $id ID оффера
     *
     * @return ?array Массив с ответом
     */
    protected function apiRequest(int $id): ?array
    {
        $response = $this->client
            ->get($this->url, [
                'access-token' => $this->key,
                'id' => $id,
            ])
            ->send()
        ;

        if (!$response->isOk) {
            \Yii::warning('Request to API failed');
            \Yii::warning($response);

            return null;
        }

        return $response->data;
    }
}
