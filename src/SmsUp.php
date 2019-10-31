<?php

namespace SquareetLabs\LaravelSmsUp;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SmsUp
 * @package SquareetLabs\LaravelSmsUp
 */
class SmsUp
{
    /**
     * @const The API URL for SmsUp
     */
    const API_URI = 'https://api.gateway360.com/api/';

    /**
     * @const The API endpoint to send messages
     */
    const ENDPOINT_SEND = '3.0/sms/send';

    /**
     * @const The API endpoint to verify phone number
     */
    const ENDPOINT_VERIFY = 'hlr/request';

    /**
     * @const The API endpoint to get account balance
     */
    const ENDPOINT_BALANCE = '3.0/account/get-balance';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var
     */
    private $config;

    /**
     * SmsUp constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->client = new Client();
        $this->config = $config;
    }

    /**
     * @param array $messages
     * @param string $reportUrl
     * @return ResponseInterface
     */
    public function sendMessages(array $messages, $reportUrl = '')
    {
        $data = [
            'api_key' => $this->config['key'],
            'concat' => 1,
            'fake' => $this->config['test_mode'] ? 1 : 0,
            'messages' => $messages
        ];
        if (!empty($reportUrl))
            $data['report_url'] = $reportUrl;
        $response = $this->client->post(self::API_URI . self::ENDPOINT_SEND, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'body' => \GuzzleHttp\json_encode($data),
        ]);

        return $response;
    }

    /**
     * @param string $phone
     * @return bool
     */
    public function verifyPhone($phone)
    {
        $data = [
            'api_key' => $this->config['key'],
            'msisdn' => $phone
        ];
        $response = $this->client->post(self::API_URI . self::ENDPOINT_VERIFY, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'body' => \GuzzleHttp\json_encode($data),
        ]);

        $responseArray = [];
        array_push($responseArray, \GuzzleHttp\json_decode($response->getBody(), true));
        $responseArray = $responseArray[0];

        if (isset($responseArray['status']) && $responseArray['status'] == 'ok') {
            return $responseArray['result']['success'];
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getBalance()
    {
        $data = [
            'api_key' => $this->config['key']
        ];
        $response = $this->client->post(self::API_URI . self::ENDPOINT_BALANCE, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'body' => \GuzzleHttp\json_encode($data),
        ]);

        $responseArray = [];
        array_push($responseArray, \GuzzleHttp\json_decode($response->getBody(), true));
        $responseArray = $responseArray[0];

        if (isset($responseArray['status']) && $responseArray['status'] == 'ok') {
            return $responseArray['result']['balance'];
        } else {
            return 'error';
        }
    }
}