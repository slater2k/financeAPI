<?php

namespace App\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class YahooFinanceApiClient implements FinanceApiClientInterface
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

    /**
     * @param $symbols
     * @param $region
     * @return JsonResponse
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function fetchStockProfile($symbols, $region): JsonResponse
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

        if($response->getStatusCode() !== 200) {
            return New JsonResponse('Finance Api Client Error', 400);
        }

        $stockProfile = json_decode($response->getContent())->quoteResponse->result[0] ?? false;

        if(!$stockProfile) {
            trigger_error('No results founds', E_USER_ERROR);
        }

        $stockProfileAsArray = [
            'symbol' => $stockProfile->symbol,
            'shortName' => $stockProfile->shortName,
            'region' => $region,
            'exchangeName' => $stockProfile->fullExchangeName,
            'currency' => $stockProfile->currency,
            'price' => $stockProfile->regularMarketPrice,
            'previousClose' => $stockProfile->regularMarketPreviousClose,
            'priceChange' => $stockProfile->regularMarketPrice - $stockProfile->regularMarketPreviousClose,
        ];

        return new JsonResponse($stockProfileAsArray, 200);
    }
}