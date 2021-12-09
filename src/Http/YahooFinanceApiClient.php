<?php

namespace App\Http;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class YahooFinanceApiClient
{
    private $httpClient;
    private $rapidApiKey;

    private const URL = 'https://stock-data-yahoo-finance-alternative.p.rapidapi.com/v6/finance/quote';
    private const X_RAPID_API_HOST = 'stock-data-yahoo-finance-alternative.p.rapidapi.com';

    public function __construct(HttpClientInterface $httpClient, $rapidApiKey)
    {
        $this->httpClient = $httpClient;
        $this->rapidApiKey = $rapidApiKey;
    }

    public function fetchStockProfile($symbols, $region): array
    {
        $response = $this->httpClient->request('GET', self::URL, [
            'query' => [
                'symbols' => $symbols,
                'region' => $region,
            ],
            'headers' => [
                'x-rapidapi-host' => self::X_RAPID_API_HOST,
                'x-rapidapi-key' => $this->rapidApiKey,
            ],
        ]);

        $stockProfile = json_decode($response->getContent())->price;
        dd($stockProfile);

        $stockProfileAsArray = [
            'symbol' => 'AMZN',
            'shortName' => 'Amazon.com, Inc.',
            'region' => 'US',
            'exchangeName' => 'NasdaqGS',
            'currency' => 'USD',
            'price' => 100.50,
            'previousClose' => 110.20,
            'priceChange' => - 9.70,
        ];

        return [
            'statusCode' => 200,
            'content' => json_encode()
        ];

    }
}