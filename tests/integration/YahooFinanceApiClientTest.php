<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;

class YahooFinanceApiClientTest extends DatabaseDependantTestCase
{
    /**
     * @test
     * @group integration
     */
    public function the_yahoo_finance_api_client_returns_the_correct_data()
    {
        // setup
        // need api client
        $yahooFinanceClient = self::$kernel->getContainer()->get('yahoo-finance-api-client');

        // do something
        $response = $yahooFinanceClient->fetchStockProfile('ETH-USD', 'US'); // symbol, region
        $stockProfile = json_decode($response['content']);

        // asserts
        $this->assertSame('AMZN', $stockProfile->symbol);
        $this->assertSame('Amazon.com, Inc.', $stockProfile->shortName);
        $this->assertSame('US', $stockProfile->region);
        $this->assertSame('NasdaqGS', $stockProfile->exchangeName);
        $this->assertSame('USD', $stockProfile->currency);
        $this->assertIsFloat($stockProfile->price);
        $this->assertIsFloat($stockProfile->previousClose);
        $this->assertIsFloat($stockProfile->priceChange);
    }
}