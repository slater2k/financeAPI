<?php

namespace App\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

class FakeYahooFinanceApiClient implements FinanceApiClientInterface
{
    public static int $statusCode = 200;
    public static string $content = '';

    public function fetchStockProfile(string $symbol, string $region): JsonResponse
    {
        return new JsonResponse(self::$content, self::$statusCode, [], $json = true);
    }

    public static function setContent(array $overrides = []): void
    {
        self::$content = json_encode(array_merge([
            'symbol' => 'AMZN',
            'region' => 'US',
            'exchange_name' => 'NasdaqGS',
            'currency' => 'USD',
            'short_name' => 'Amazon.com, Inc.',
            'previous_close' => 3172.69,
            'price' => 3258.7083,
            'price_change' => 86.02
        ], $overrides));
    }
}