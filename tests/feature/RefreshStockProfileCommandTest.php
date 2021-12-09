<?php

namespace App\Tests\Feature;

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
    public function the_refresh_stock_profile_command_behaves_when_a_stock_record_does_not_exist()
    {
        //setup
        $application = new Application(self::$kernel);
        $command = $application->find('app:refresh-stock-profile');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US'
        ]);

        $repo = $this->entityManager->getRepository(Stock::class);

        /**
         * @var Stock $stock
         */
        $stock = $repo->findOneBy(['symbol' => 'AMZN']);

        $this->assertSame('USD', $stock->getCurrency());
        $this->assertSame('NasdaqGS', $stock->getExchangeName());
        $this->assertSame('AMZN', $stock->getSymbol());
        $this->assertSame('Amazon.com, Inc.', $stock->getShortName());
        $this->assertSame('US', $stock->getRegion());
        $this->assertGreaterThan(50, $stock->getPreviousClose());
        $this->assertGreaterThan(50, $stock->getPrice());
    }
}