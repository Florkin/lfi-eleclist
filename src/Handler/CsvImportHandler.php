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
use App\Repository\AddressRepository;
use App\Repository\ElectorRepository;
use DateTime;
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
    private array $config;

    /** @var array */
    private array $failedRecords = [];

    /** @var array */
    private array $apptOccurence = [];

    /** @var AddressRepository */
    private AddressRepository $addressRepository;

    /** @var SymfonyStyle  */
    private SymfonyStyle $io;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ElectorRepository $electorRepository
     * @param CsvHandler $csvHandler
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ElectorRepository $electorRepository,
        CsvHandler $csvHandler,
        AddressRepository $addressRepository,
        array $config
    ) {
        $this->entityManager = $entityManager;
        $this->electorRepository = $electorRepository;
        $this->csvHandler = $csvHandler;
        $this->config = $config;
        $this->addressRepository = $addressRepository;
    }

    public function importFile(string $filePath, string $delimiter = ',', int $offset = 0): array
    {
        $this->csvHandler->createReader($filePath, $delimiter, $offset);
        $this->csvHandler->mapHeader($delimiter, $offset);
        $reader = $this->csvHandler->getReader();
        $records = $reader->getRecords();

        $counter = 0;
        $successCounter = 0;
        $failCounter = 0;

        $progressBar = $this->io->createProgressBar($reader->count());

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

            $progressBar->finish();
            $this->io->newLine(3);
            $this->setApptCounts();

        } catch (Exception $e) {
            throw new CsvFormatException($e->getMessage());
        }

        return ['success' => $successCounter, 'error' => $failCounter];
    }

    public function clear()
    {
        $electors = $this->electorRepository->findAll();
        $this->io->section('clearing electors...');
        $progressBar = $this->io->createProgressBar(count($electors));

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
        $this->io->newLine(3);
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

        $this->apptOccurence[] = trim($address->getNumber()) . '_'
            . trim($address->getStreet()) . '_'
            . trim($address->getCity()) . '|'
            . trim($record['add1']);

        return true;
    }

    /**
     * @throws Exception
     */
    private function createElector(array $record): ?Elector
    {
        if (!$record['vote_office']) {
            return null;
        }

        $lastName = $this->getLastName($record);
        $birthdate = isset($record['birthdate']) && $record['birthdate']
            ? DateTime::createFromFormat('d/m/Y', $record['birthdate'])
            : null;

        $elector = new Elector();
        $elector
            ->setFirstname(trim($record['firstname']))
            ->setLastname(trim($lastName))
            ->setBirthname(trim($record['birthname']))
            ->setVoteOffice(trim($record['vote_office']))
            ->setBirthdate($birthdate);

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

        if (isset($this->config['only_reliable_data']) && $this->config['only_reliable_data']) {
            return null;
        }

        if (
            !preg_match("/[a-zA-Z]/i", trim($record['house_number']))
            && preg_match("/[0-9]/i", trim($record['house_number']))
            && !strlen(trim($record['house_number']) > 3)
        ) {
            return $record['house_number'];
        }

        if (str_contains(strtoupper($record['house_number']), 'BIS')
            || str_contains(strtoupper($record['house_number']), 'B')) {
            return (int)$record['house_number'] . 'b';
        }

        if (str_contains(strtoupper($record['house_number']), 'TER')
            || str_contains(strtoupper($record['house_number']), 'T')) {
            return (int)$record['house_number'] . 't';
        }

        return null;
    }

    private function setApptCounts()
    {
        $counts = array_diff(array_count_values($this->apptOccurence), [1]);
        $this->io->section('Counting door occurences');
        $progress = $this->io->createProgressBar(count($counts));

        foreach ($counts as $key => $count) {
            $appt = explode('|', $key) [1];
            $number = explode('_', $key)[0];
            $street = explode('_', str_replace('|' . $appt, '', $key))[1];

            $addresses = $this->addressRepository->findBy([
                'add1' => $appt,
                'number' => $number,
                'street' => $street
            ]);

            foreach ($addresses as $address) {
                $address->setApptOccurences($count);
                $this->entityManager->persist($address);
            }

            $progress->advance();
        }

        $this->entityManager->flush();
        $progress->finish();
    }

    public function setIo(SymfonyStyle $io)
    {
        $this->io = $io;
        return $this;
    }
}
