<?php

namespace app\services\emailer\db;

use app\services\emailer\OfferMessage;
use app\services\emailer\interfaces\OfferInterface;
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
            ->from('tbl_post_original')
            ->where(['city_id' => $city, 'hidden_from_site' => 0])
            ->andWhere(['>', 'endDate', date('Y-m-d H:i:s')])
            ->orderBy(['priority' => SORT_DESC])
            ->one();

        if (!$offer) {
            return null;
        }

        $response = $this->client
            ->get($this->url, [
                'access-token' => $this->key,
                'id' => $offer['id']
            ])
            ->send();
        $data = $response->data;

        // TODO: What test should be composed for tours? What data to use?
        $content = [];
        foreach ($data['tours'] as $tour) {
            $content[] = $tour['type'];
        }

        return (new OfferMessage($offer['title'], implode("\n", $content)));
    }
}
