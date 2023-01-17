<?php

namespace app\services\emailer;

use app\services\emailer\interfaces\AudienceInterface;
use app\services\emailer\interfaces\OfferInterface;
use app\services\emailer\interfaces\QueueStoreInterface;

class Queue
{
    public function __construct(private QueueStoreInterface $store)
    {
    }

    public function queueOfferToAudience(string $city, OfferInterface $offer, AudienceInterface $audience): int
    {
        $count = 0;
        $offerMessage = $offer->find($city);

        /** @var Subscriber $sub */
        foreach ($audience->findAll($city) as $sub) {
            $qm = new QueueMessage($sub->id, $sub->email, $offerMessage->title, $offerMessage->content);
            if ($this->store->send($qm)) {
                ++$count;
            }
        }

        return $count;
    }
}
