<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\Stock;
use App\Http\YahooFinanceApiClient;

class RefreshStockProfileCommand extends Command
{
    protected static $defaultName = 'app:refresh-stock-profile';
    protected static $defaultDescription = 'Add a short description for your command';

    private $entityManager;
    private $yahooFinanceApiClient;

    public function __construct(EntityManagerInterface $entityManager, YahooFinanceApiClient $yahooFinanceApiClient)
    {
        $this->entityManager = $entityManager;
        $this->yahooFinanceApiClient = $yahooFinanceApiClient;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('symbol', InputArgument::REQUIRED, 'Stock symbol e.g. AMZN for Amazon')
            ->addArgument('region', InputArgument::REQUIRED, 'The region of the company e.g. US for United States')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // ping yahoo api and grab response ( a stock profile )
        $stockProfile = $this->yahooFinanceApiClient->fetchStockProfiles(
            $input->getArgument('symbol'),
            $input->getArgument('region')
        );

        /**
         * TODO - come back and refactor, not very OOP
         */
        if($stockProfile['statusCode'] !== 200) {
            // handle non 200's
        }

        $stock = $this->serializer->deserialize($stockProfile['content'], Stock::class, 'json');

        // use stock profile response to update a record if exists

        // use response to create a record if it does exist

        $stock = new Stock();

        $stock->setCurrency($stockProfile->currency);
        $stock->setExchangeName($stockProfile->exchangeName);
        $stock->setSymbol($stockProfile->symbol);
        $stock->setShortName($stockProfile->shortName);
        $stock->setRegion($stockProfile->region);
        $stock->setPreviousClose($stockProfile->previousClose);
        $stock->setPrice($stockProfile->price);

        $priceChange = $stockProfile->price - $stockProfile->previousClose;
        $stock->setPriceChange($priceChange);

        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
