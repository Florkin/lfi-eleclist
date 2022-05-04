<?php

namespace App\Command;

use App\Exception\CsvFormatException;
use App\Handler\CsvImportHandler;
use App\Handler\AddressRequestHandler;
use App\Handler\CsvHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportCsvCommand extends Command
{
    protected static $defaultName = 'eleclist:import-csv';

    protected static $defaultDescription = 'Import an electoral list from csv';

    /** @var CsvHandler */
    private CsvHandler $csvHandler;

    /** @var CsvImportHandler */
    private CsvImportHandler $recordHandler;

    /** @var AddressRequestHandler */
    private AddressRequestHandler $addressRequest;

    /**
     * @param CsvHandler $csvHandler
     * @param CsvImportHandler $recordHandler
     * @param AddressRequestHandler $addressRequest
     * @param string|null $name
     */
    public function __construct(
        CsvHandler $csvHandler,
        CsvImportHandler $recordHandler,
        AddressRequestHandler $addressRequest,
        string $name = null
    ) {
        $this->csvHandler = $csvHandler;
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
            $this->recordHandler->clear($io);
        }

        $io->section(
            'Requesting official address data from https://adresse.data.gouv.fr/api-doc/adresse, '
            . 'this can be very long (More than 10 minutes for 50k lines)'
        );
        $newCsvString = $this->addressRequest->request($filePath, $delimiter);
        $io->info("Response from API finally received! Thanks for your patience.");
        $io->newLine();

        $io->info('Saving new CSV...');
        $newFilePath = $this->csvHandler->saveCsv($newCsvString, $delimiter);
        $io->newLine();

        $io->section('Data import');
        $result = $this->recordHandler->importFile($newFilePath, $io);
        $this->csvHandler->archiveFailedFromArray($this->recordHandler->getFailedRecords());

        $io->info('Deleting temporary csv...');
        $this->csvHandler->archive($newFilePath);

        $io->section("Resultat de l'import");
        $successLines = $result['success'];
        $failedLines = $result['error'];
        $io->success("$successLines electors imported");
        $percentFails = round(($failedLines * 100) / ($successLines + $failedLines), 2);
        $io->warning(
            "$failedLines electors ($percentFails %) failed to import, "
            . "due to incomplete data (house_number, city and street are required)"
        );

        return Command::SUCCESS;
    }
}
