<?php
/**
 * ElecListCsvRecordHandler.php
 *
 * @author    Tristan Florin <tristan.florin@smile.fr>
 * @copyright 2022 Smile
 */

namespace App\Handler;

use App\Entity\Address;
use App\Entity\Elector;
use App\Repository\ElectorRepository;
use App\Service\CsvReader;
use Doctrine\ORM\EntityManagerInterface;

class ElecListCsvRecordHandler
{
    /** @var EntityManagerInterface */
    private EntityManagerInterface $entityManager;

    /** @var ElectorRepository */
    private ElectorRepository $electorRepository;

    /** @var CsvReader */
    private CsvReader $csvReader;

    /** @var array  */
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
        CsvReader $csvReader,
        array $params
    ) {
        $this->entityManager = $entityManager;
        $this->electorRepository = $electorRepository;
        $this->csvReader = $csvReader;
        $this->params = $params;
    }

    public function importFile(String $filePath)
    {
        $records = $this->csvReader->getRecords($filePath);
        $count = $this->csvReader->getReader()->count();

        $counter = 0;

        foreach ($records as $key => $record) {
            $this->saveRecord($record);

            if ($counter === 1000 || $key === $count) {
                $this->entityManager->flush();
                $counter = 0;
                continue;
            }

            $counter += 1;
        };
    }

    public function clear()
    {
        foreach ($this->electorRepository->findAll() as $elector) {
            $this->entityManager->remove($elector);
        }

        $this->entityManager->flush();
    }

    private function saveRecord(array $record)
    {
        $elector = $this->createElector($record);
        $elector->setAddress($this->createAddress($record));
        $this->entityManager->persist($elector);
    }

    private function createElector(array $record)
    {
        $lastName = $this->getLastName($record);

        $elector = new Elector();
        $elector
            ->setFirstname(trim($record['firstname']))
            ->setLastname(trim($lastName))
            ->setBirthname(trim($record['birthname']))
            ->setVoteOffice(trim($record['vote_office']));

        return $elector;
    }

    private function createAddress(array $record)
    {
        $address = new Address();
        $address
            ->setAdd1(trim($record['add1']))
            ->setAdd2(trim($record['add2']))
            ->setStreet(trim($record['result_name']?? $record['street']))
            ->setCity(trim($record['result_city'] ?? $record['city']))
            ->setPostcode(trim($record['result_postcode'] ?? $record['postcode']))
            ->setNumber(trim($record['result_housenumber'] ?? $record['house_number']));

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
