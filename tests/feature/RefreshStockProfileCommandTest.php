<?php

namespace App\Tests\Feature;

use App\Http\FakeYahooFinanceApiClient;
use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use App\Entity\Stock;
use App\Tests\DatabaseDependantTestCase;

class RefreshStockProfileCommandTest extends DatabaseDependantTestCase
{
    /**
     * @test
     */
    public function the_refresh_stock_profile_command_creates_new_records_correctly()
    {
        //setup
        $application = new Application(self::$kernel);
        $command = $application->find('app:refresh-stock-profile');
        $commandTester = new CommandTester($command);

        FakeYahooFinanceApiClient::$content = '{"symbol":"AMZN","shortName":"Amazon.com, Inc.","region":"US","exchangeName":"NasdaqGS","currency":"USD","price":3483.42,"previousClose":3523.16,"priceChange":-39.73999999999978}';

        $commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US'
        ]);

        $stockRepository = $this->entityManager->getRepository(Stock::class);

        /**
         * @var Stock $stock
         */
        $stock = $stockRepository->findOneBy(['symbol' => 'AMZN']);

        $this->assertSame('USD', $stock->getCurrency());
        $this->assertSame('NasdaqGS', $stock->getExchangeName());
        $this->assertSame('AMZN', $stock->getSymbol());
        $this->assertSame('Amazon.com, Inc.', $stock->getShortName());
        $this->assertSame('US', $stock->getRegion());
        $this->assertGreaterThan(50, $stock->getPreviousClose());
        $this->assertGreaterThan(50, $stock->getPrice());
        $this->assertStringContainsString('Amazon.com, Inc. has been saved / updated', $commandTester->getDisplay());
    }

    /**
     * @test
     */
    public function the_refresh_stock_profile_command_updates_existing_records_correctly()
    {
        $stock = new Stock();
        $stock->setSymbol('AMZN');
        $stock->setRegion('US');
        $stock->setExchangeName('NasdaqGS');
        $stock->setCurrency('USD');
        $stock->setPreviousClose(3000);
        $stock->setPrice(3100);
        $stock->setPriceChange(100);
        $stock->setShortName('Amazon.com, Inc.');

        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        $stockId = $stock->getId();

        $application = new Application(self::$kernel);
        $command = $application->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        FakeYahooFinanceApiClient::$statusCode = 200;
        FakeYahooFinanceApiClient::setContent([
            'previous_close' => 100,
            'price' => 200,
            'price_change' => 100
        ]);

        $commandStatus = $commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US'
        ]);

        $repo = $this->entityManager->getRepository(Stock::class);

        $stockRecord = $repo->find($stockId);

        $this->assertEquals(100, $stockRecord->getPreviousClose());
        $this->assertEquals(200, $stockRecord->getPrice());
        $this->assertEquals(100, $stockRecord->getPriceChange());

        $stockRecordCount = $repo->createQueryBuilder('stock')
            ->select('count(stock.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $this->assertEquals(0, $commandStatus);

        // Check no duplicates, 1 record rather than 2
        $this->assertEquals(1, $stockRecordCount);
    }

    /**
     * @test
     */
    public function non_200_status_code_responses_are_handled_correctly()
    {
        $application = new Application(self::$kernel);
        $command = $application->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        FakeYahooFinanceApiClient::$statusCode = 500;
        FakeYahooFinanceApiClient::$content = 'Finance Api Client Error';

        $commandStatus = $commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US'
        ]);

        $repo = $this->entityManager->getRepository(Stock::class);
        $stockRecordCount = $repo->createQueryBuilder('stock')
            ->select('count(stock.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $this->assertEquals(1, $commandStatus);
        $this->assertEquals(0, $stockRecordCount);
        $this->assertStringContainsString('Finance Api Client Error', $commandTester->getDisplay());
    }
}