<?php

/**
 * CsvImportHandler
 *
 * @author    Tristan Florin <tristan.florin@smile.fr>
 * @copyright 2022 Smile
 */

namespace App\Handler;

use App\Entity\Address;
use App\Entity\Elector;
use App\Exception\CsvFormatException;
use App\Repository\ElectorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Style\SymfonyStyle;

class CsvImportHandler
{
    /** @var EntityManagerInterface */
    private EntityManagerInterface $entityManager;

    /** @var ElectorRepository */
    private ElectorRepository $electorRepository;

    /** @var CsvHandler */
    private CsvHandler $csvHandler;

    /** @var array */
    private array $params;

    /** @var array  */
    private array $failedRecords = [];

    /**
     * @param EntityManagerInterface $entityManager
     * @param ElectorRepository $electorRepository
     * @param CsvHandler $csvHandler
     * @param array $params
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ElectorRepository $electorRepository,
        CsvHandler $csvHandler,
        array $params
    ) {
        $this->entityManager = $entityManager;
        $this->electorRepository = $electorRepository;
        $this->csvHandler = $csvHandler;
        $this->params = $params;
    }

    public function importFile(string $filePath, SymfonyStyle $io, string $delimiter = ',', int $offset = 0): array
    {
        $this->csvHandler->createReader($filePath, $delimiter, $offset);
        $this->csvHandler->mapHeader($delimiter, $offset);
        $reader = $this->csvHandler->getReader();
        $records = $reader->getRecords();

        $counter = 0;
        $successCounter = 0;
        $failCounter = 0;

        $progressBar = $io->createProgressBar($reader->count());

        try {
            foreach ($records as $record) {
                if ($this->saveRecord($record)) {
                    $successCounter += 1;
                } else {
                    $failCounter += 1;
                    $this->addRecordToFails($record);
                }

                if ($counter === 1000) {
                    $this->entityManager->flush();
                    $counter = 0;
                    continue;
                }

                $counter += 1;
                $progressBar->advance();
            }

            $this->entityManager->flush();

        } catch (Exception $e) {
            $message =
                'There was an error importing CSV file. Check delimiter '
                . 'and change it if necessary (--delimiter=";"). '
                . 'If it doesn\'t work, try open it and save it as CSV '
                . 'with spreadsheet program like Excel or LibreOffice. '
                . 'You can check CSV file with https://www.toolkitbay.com/tkb/tool/csv-validator';
            throw new CsvFormatException($message);
        }

        $progressBar->finish();
        $io->newLine(3);
        return ['success' => $successCounter, 'error' => $failCounter];
    }

    public function clear(SymfonyStyle $io)
    {
        $electors = $this->electorRepository->findAll();
        $io->section('clearing electors...');
        $progressBar = $io->createProgressBar(count($electors));

        $counter = 0;
        foreach ($electors as $elector) {
            $this->entityManager->remove($elector);
            $progressBar->advance();

            if ($counter === 1000) {
                $this->entityManager->flush();
                $counter = 0;
                continue;
            }

            $counter += 1;
        }

        $this->entityManager->flush();

        $progressBar->finish();
        $io->newLine(3);
    }

    public function getFailedRecords(): array
    {
        return $this->failedRecords;
    }

    private function saveRecord(array $record): bool
    {
        $elector = $this->createElector($record);
        $address = $this->createAddress($record);
        if (!$elector || !$address) {
            return false;
        }

        $elector->setAddress($address);
        $this->entityManager->persist($elector);

        return true;
    }

    private function createElector(array $record): ?Elector
    {
        if (!$record['vote_office']) {
            return null;
        }

        $lastName = $this->getLastName($record);

        $elector = new Elector();
        $elector
            ->setFirstname(trim($record['firstname']))
            ->setLastname(trim($lastName))
            ->setBirthname(trim($record['birthname']))
            ->setVoteOffice(trim($record['vote_office']));

        return $elector;
    }

    private function createAddress(array $record): ?Address
    {
        if (!$record['result_name']
            || !$record['result_housenumber'] && !$record['house_number']
            || !$record['result_city']) {
            return null;
        }

        $houseNumber = $this->formatHouseNumber($record);
        if (!$houseNumber) {
            return null;
        }

        $address = new Address();
        $address
            ->setAdd1(trim($record['add1']))
            ->setAdd2(trim($record['add2']))
            ->setStreet(trim($record['result_name']))
            ->setCity(trim($record['result_city']))
            ->setPostcode(trim($record['result_postcode']))
            ->setNumber($houseNumber);

        return $address;
    }

    private function getLastName(array $record)
    {
        if ($record['lastname']) {
            return $record['lastname'];
        }

        return $record['birthname'];
    }

    private function addRecordToFails($record)
    {
        $this->failedRecords[] = $record;
    }

    private function formatHouseNumber(array $record): ?string
    {
        if ($record['result_housenumber']) {
            return $record['result_housenumber'];
        }

        if (
            !preg_match("/[a-zA-Z]/i", $record['house_number'])
            && preg_match("/[0-9]/i", $record['house_number'])
        ) {
            return $record['house_number'];
        }

        if (str_contains(strtoupper($record['house_number']), 'BIS')
            || str_contains(strtoupper($record['house_number']), 'B')) {
            return (int) $record['house_number'] . 'b';
        }

        if (str_contains(strtoupper($record['house_number']), 'TER')
            || str_contains(strtoupper($record['house_number']), 'T')) {
            return (int) $record['house_number'] . 't';
        }

        return null;
    }
}
