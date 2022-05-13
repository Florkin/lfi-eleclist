<?php

namespace App\Command;

use App\Entity\GroupedAddress;
use App\Exception\CsvFormatException;
use App\Handler\CsvImportHandler;
use App\Handler\AddressRequestHandler;
use App\Handler\CsvHandler;
use App\Handler\PdfHandler;
use App\Repository\ElectorRepository;
use App\Repository\GroupedAddressRepository;
use App\Service\Zipper;
use PhpZip\ZipFile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

class PdfGenerateCommand extends Command
{
    protected static $defaultName = 'eleclist:generate-pdf';

    protected static $defaultDescription = 'Generate PDF';

    private ElectorRepository $electorRepository;
    private GroupedAddressRepository $groupedAddressRepository;
    private PdfHandler $pdfHandler;
    private Filesystem $filesystem;
    private EntrypointLookupInterface $entrypointLookup;
    private array $pdfConfig;

    public function __construct(
        ElectorRepository $electorRepository,
        GroupedAddressRepository $groupedAddressRepository,
        PdfHandler $pdfHandler,
        Filesystem $filesystem,
        EntrypointLookupInterface $entrypointLookup,
        string $name = null,
        array $pdfConfig = []
    ) {
        $this->electorRepository = $electorRepository;
        $this->groupedAddressRepository = $groupedAddressRepository;
        $this->pdfHandler = $pdfHandler;
        $this->pdfConfig = $pdfConfig;
        $this->filesystem = $filesystem;
        $this->entrypointLookup = $entrypointLookup;

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('cities', InputArgument::IS_ARRAY, 'Cities to export');
        $this->addOption('zip', 'z', InputOption::VALUE_NONE, 'Zip Pdf ?');
    }

    /**
     * @throws \PhpZip\Exception\ZipException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pdfPath = $this->pdfConfig['pdf_output_path'];
        $io = new SymfonyStyle($input, $output);
        $cities = $input->getArgument('cities');
        $zip = $input->getOption('zip');
        $toZip = [];

        $io->section('Generating PDF\'s');

        foreach ($cities as $city) {
            $io->info('Generating PDF for ' . $city);
            $streets = $this->groupedAddressRepository->findStreets($city)->execute();

            $this->entrypointLookup->reset();

            $filename = $pdfPath . '/' . $city . '.pdf';
            $this->pdfHandler->generatePdf($streets, $city, $filename);

            if ($zip) {
                $toZip[] = $filename;
            }

            $io->newLine();
            if (!$zip) {
                $io->success('PDF\'s generated in public/pdf/' . $city);
            }
        }

        if ($zip) {
            $io->section('Zipping files...');
            $dateNow = (new \DateTime('now'))->format('d.m.Y-h:m');
            $zipName = 'pdf_' . $dateNow . '.zip';
            $zipFile = new ZipFile();

            foreach ($toZip as $filename) {
                $zipFile->addFile($filename);
            }

            $zipFile->saveAsFile($pdfPath . '/' . $zipName);

            foreach ($toZip as $filename) {
                $this->filesystem->remove($filename);
            }
        }

        $io->success('PDF\'s generated in public/pdf/' . $zipName);

        return 0;
    }
}
