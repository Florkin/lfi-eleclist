<?php

namespace App\Command;

use App\Exception\CsvFormatException;
use App\Handler\ElecListCsvImporter;
use App\Handler\AddressRequestHandler;
use App\Service\CsvReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EleclistImportCsvCommand extends Command
{
    protected static $defaultName = 'eleclist:import-csv';

    protected static $defaultDescription = 'Import an electoral list from csv';

    /** @var CsvReader */
    private CsvReader $csvReader;

    /** @var ElecListCsvImporter */
    private ElecListCsvImporter $recordHandler;

    /** @var AddressRequestHandler */
    private AddressRequestHandler $addressRequest;

    /**
     * @param CsvReader $csvReader
     * @param ElecListCsvImporter $recordHandler
     * @param AddressRequestHandler $addressRequest
     * @param string|null $name
     */
    public function __construct(
        CsvReader $csvReader,
        ElecListCsvImporter $recordHandler,
        AddressRequestHandler $addressRequest,
        string $name = null
    ) {
        $this->csvReader = $csvReader;
        $this->recordHandler = $recordHandler;
        $this->addressRequest = $addressRequest;

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Csv file path')
            ->addOption(
                'clear',
                'c',
                InputOption::VALUE_NONE,
                'Clear all before import'
            )
            ->addOption(
                'delimiter',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Csv delimiter',
                ','
            );
    }

    /**
     * @throws CsvFormatException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');
        $delimiter = $input->getOption('delimiter');

        if ($input->getOption('clear')) {
            $io->info('Clearing database...');
            $this->recordHandler->clear();
        }

        $io->info('Requesting official address data from https://adresse.data.gouv.fr/api-doc/adresse...');
        $newCsvString = $this->addressRequest->request($filePath, $delimiter);

        $io->info('Saving new CSV...');
        $newFilePath = $this->csvReader->saveCsv($newCsvString, $delimiter);

        $io->info('Importing...');
        if ($this->recordHandler->importFile($newFilePath)) {
            $io->info('Deleting temporary csv...');
            $this->csvReader->delete($newFilePath);
        }

        $io->success('CSV is successfully imported !');

        return Command::SUCCESS;
    }
}
