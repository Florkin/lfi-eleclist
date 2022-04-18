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
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\MapIterator;
use League\Csv\Reader;

class ElecListCsvRecordHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function importFile(Reader $file)
    {
        $count = $file->count();
        $counter = 0;

        foreach ($file->getRecords() as $key => $record) {
            $this->saveRecord($record);

            if ($counter === 1000 || $key === $count) {
                $this->entityManager->flush();
                $counter = 0;
                continue;
            }

            $counter += 1;
        };
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
            ->setFirstname(trim($record['prénoms']))
            ->setLastname(trim($lastName))
            ->setBirthname(trim($record['nom de naissance']))
            ->setVoteOffice(trim($record['code du bureau de vote']));

        return $elector;
    }

    private function createAddress(array $record)
    {
        $address = new Address();
        $address
            ->setAdd1(trim($record['complément 1']))
            ->setAdd2(trim($record['complément 2']))
            ->setStreet(trim($record['libellé de voie']))
            ->setCity(trim($record['commune']))
            ->setPostcode(trim($record['code postal']))
            ->setNumber(trim($record['numéro de voie']));

        return $address;
    }

    private function getLastName(array $record)
    {
        if ($record['nom d\'usage']) {
            return $record['nom d\'usage'];
        }

        return $record['nom de naissance'];
    }
}
