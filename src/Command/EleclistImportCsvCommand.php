<?php

namespace App\Command;

use App\Handler\ElecListCsvRecordHandler;
use App\Service\CsvReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EleclistImportCsvCommand extends Command
{
    protected static $defaultName = 'eleclist:import-csv';

    protected static $defaultDescription = 'Import an electoral list from csv';

    /**
     * @var EleclistImportCsvCommand
     */
    private $csvReader;
    /**
     * @var ElecListCsvRecordHandler
     */
    private $recordHandler;

    /**
     * @param CsvReader $csvReader
     * @param ElecListCsvRecordHandler $recordHandler
     * @param string|null $name
     */
    public function __construct(CsvReader $csvReader, ElecListCsvRecordHandler $recordHandler, string $name = null)
    {
        parent::__construct($name);

        $this->csvReader = $csvReader;
        $this->recordHandler = $recordHandler;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'Csv file path')
            ->addOption(
                'clear',
                'c',
                InputArgument::OPTIONAL,
                'Clear all before import (0 or 1)',
                0
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('file');
        $clear = $input->getOption('clear');

        $this->recordHandler->importFile($this->csvReader->getFile($filePath));

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
