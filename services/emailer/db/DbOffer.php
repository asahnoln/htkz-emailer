<?php

namespace app\services\emailer\db;

use app\services\emailer\interfaces\OfferInterface;
use app\services\emailer\OfferMessage;
use yii\db\Query;
use yii\httpclient\Client;

class DbOffer implements OfferInterface
{
    public function __construct(private Client $client, private string $url, private string $key)
    {
    }

    public function find(string $city): ?OfferMessage
    {
        $offer = (new Query())
            ->select(['id', 'title', 'priority'])
            ->from('{{%post_original}}')
            ->where(['city_id' => $city, 'hidden_from_site' => 0])
            ->andWhere(['>', 'endDate', date('Y-m-d H:i:s')])
            ->orderBy(['priority' => SORT_DESC])
            ->one()
        ;

        if (!$offer) {
            \Yii::warning("Offer was not found in DB for city {$city}");

            return null;
        }

        $response = $this->client
            ->get($this->url, [
                'access-token' => $this->key,
                'id' => $offer['id'],
            ])
            ->send()
        ;

        if (!$response->isOk) {
            \Yii::warning('Request to API failed');
            \Yii::warning($response);

            return null;
        }

        $data = $response->data;

        // TODO: What price should be used? ForTour
        // TODO: Move out to a template
        $content = [];
        foreach ($data['tours'] as $tour) {
            $content[] = "{$tour['hotel']['name']} - {$tour['price']['forTour']}";
        }

        return new OfferMessage($offer['title'], implode("\n", $content));
    }
}
