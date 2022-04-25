<?php

namespace App\Command;

use App\Handler\ElecListCsvRecordHandler;
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

    /** @var ElecListCsvRecordHandler */
    private ElecListCsvRecordHandler $recordHandler;

    /** @var AddressRequestHandler */
    private AddressRequestHandler $addressRequest;

    /**
     * @param CsvReader $csvReader
     * @param ElecListCsvRecordHandler $recordHandler
     * @param AddressRequestHandler $addressRequest
     * @param string|null $name
     */
    public function __construct(
        CsvReader $csvReader,
        ElecListCsvRecordHandler $recordHandler,
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
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');

        if ($input->getOption('clear')) {
            $this->recordHandler->clear();
        }
        $newCsvString = $this->addressRequest->request($filePath);
        $newFilePath = $this->csvReader->saveCsv($newCsvString);
//        dd($newFilePath);
//        $this->recordHandler->importFile($newFilePath);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
