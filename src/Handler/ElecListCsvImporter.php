<?php

/**370890 437764 370881 437530 66874 66649
 * ElecListCsvRecordHandler.php
 *
 * @author    Tristan Florin <tristan.florin@smile.fr>
 * @copyright 2022 Smile
 */

namespace App\Handler;

use App\Entity\Address;
use App\Entity\Elector;
use App\Exception\CsvFormatException;
use App\Repository\AddressRepository;
use App\Repository\ElectorRepository;
use App\Service\CsvReader;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Style\SymfonyStyle;

class ElecListCsvImporter
{
    /** @var EntityManagerInterface */
    private EntityManagerInterface $entityManager;

    /** @var ElectorRepository */
    private ElectorRepository $electorRepository;

    /** @var AddressRepository */
    private AddressRepository $addressRepository;

    /** @var CsvReader */
    private CsvReader $csvReader;

    /** @var array */
    private array $params;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ElectorRepository $electorRepository
     * @param CsvReader $csvReader
     * @param array $params
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ElectorRepository $electorRepository,
        AddressRepository $addressRepository,
        CsvReader $csvReader,
        array $params
    ) {
        $this->entityManager = $entityManager;
        $this->electorRepository = $electorRepository;
        $this->csvReader = $csvReader;
        $this->params = $params;
        $this->addressRepository = $addressRepository;
    }

    public function importFile(string $filePath, SymfonyStyle $io, string $delimiter = ',', int $offset = 0): array
    {
        $this->csvReader->createReader($filePath, $delimiter, $offset);
        $this->csvReader->mapHeader($delimiter, $offset);
        $reader = $this->csvReader->getReader();
        $records = $reader->getRecords();

        $count = $reader->count();
        $counter = 0;
        $successCounter = 0;
        $failCounter = 0;

        $progressBar = $io->createProgressBar($count);

        try {
            foreach ($records as $key => $record) {
                if ($this->saveRecord($record)) {
                    $successCounter += 1;
                } else {
                    $failCounter += 1;
                }

                if ($counter === 10000 || $key === $count - 1) {
                    $this->entityManager->flush();
                    $counter = 0;
                    continue;
                }

                $counter += 1;
                $progressBar->advance();
            };
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

        foreach ($electors as $elector) {
            $this->entityManager->remove($elector);
            $progressBar->advance();
        }

        $this->entityManager->flush();
        $progressBar->finish();
        $io->newLine(3);

        $addresses = $this->addressRepository->findAll();
        $io->section('clearing addresses...');
        $progressBar = $io->createProgressBar(count($addresses));
        foreach ($addresses as $address) {
            $this->entityManager->remove($address);
            $progressBar->advance();
        }

        $this->entityManager->flush();
        $progressBar->finish();
        $io->newLine(3);
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
        if (!$record['result_name'] || !$record['result_housenumber'] || !$record['result_city']) {
            return null;
        }

        $address = new Address();
        $address
            ->setAdd1(trim($record['add1']))
            ->setAdd2(trim($record['add2']))
            ->setStreet(trim($record['result_name']))
            ->setCity(trim($record['result_city']))
            ->setPostcode(trim($record['result_postcode']))
            ->setNumber(trim($record['result_housenumber']));

        return $address;
    }

    private function getLastName(array $record)
    {
        if ($record['lastname']) {
            return $record['lastname'];
        }

        return $record['birthname'];
    }
}
