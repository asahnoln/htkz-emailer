<?php

namespace services;

use Codeception\Stub\Expected;
use app\services\emailer\Amplitude;
use app\services\emailer\interfaces\AnalyticsInterface;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\httpclient\Response;

class AmplitudeTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
    }

    protected function _after(): void
    {
    }

    // tests
    public function testSendToAmplitude(): void
    {
        $url = 'http://testurl.com';
        $client =  $this->make(Client::class, [
            'post' => Expected::once(function ($u, $data, $headers) use ($url) {
                verify($u)->equals($url);
                verify($data['api_key'])->equals('secretKey');
                verify($data['events'])->equals([
                    [
                        'event_type' => 'email send',
                        'device_id' => 'mail_testAmplitudeId',
                        'event_properties' => [
                            'type' => 'mailing',
                            'mail_type' => 'single',
                            'is_auto' => true,
                        ],
                    ],
                ]);
                verify($headers['content-type'])->equals('application/json');
                verify($headers['accept'])->equals('*/*');

                return $this->make(Request::class, [
                    'send' => Expected::once(function () {
                        return $this->make(Response::class, [
                            'data' => ['code' => 200],
                            'getStatusCode' => 200,
                        ]);
                    }),
                ]);
            }),
        ]);
        $a = new Amplitude($client, $url, 'secretKey');
        $result = $a->send('testAmplitudeId');

        verify($a)->instanceOf(AnalyticsInterface::class);
        verify($result)->true();
    }

    public function testReturnFalseOnBadCode(): void
    {
        $url = 'http://testurl.com';
        $client =  $this->make(Client::class, [
            'post' => Expected::once(function ($u, $data, $headers) use ($url) {
                return $this->make(Request::class, [
                    'send' => Expected::once(function () {
                        return $this->make(Response::class, [
                            'data' => ['code' => 400],
                            'getIsOk' => false,
                        ]);
                    }),
                ]);
            }),
        ]);
        $a = new Amplitude($client, $url, 'secretKey');
        $result = $a->send('testAmplitudeId');

        verify($result)->false();
    }
}
