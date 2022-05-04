<?php
/**
 * AddressGroupHandler.php
 *
 * @author    Tristan Florin <tristan.florin@smile.fr>
 * @copyright 2022 Smile
 */

namespace App\Handler;

use App\Entity\GroupedAddress;
use App\Repository\AddressRepository;
use App\Repository\ElectorRepository;
use App\Repository\GroupedAddressRepository;
use Doctrine\ORM\EntityManagerInterface;

class AddressGroupHandler
{
    private AddressRepository $addressRepository;
    private EntityManagerInterface $entityManager;
    private ElectorRepository $electorRepository;
    private GroupedAddressRepository $groupedAddressRepository;

    public function __construct(
        AddressRepository $addressRepository,
        EntityManagerInterface $entityManager,
        ElectorRepository $electorRepository,
        GroupedAddressRepository $groupedAddressRepository
    ) {
        $this->addressRepository = $addressRepository;
        $this->entityManager = $entityManager;
        $this->electorRepository = $electorRepository;
        $this->groupedAddressRepository = $groupedAddressRepository;
    }

    public function createGroupedAddress($data): GroupedAddress
    {
        $address = new GroupedAddress();
        $address
            ->setNumber($data['number'])
            ->setStreet($data['street'])
            ->setPostcode($data['postcode'])
            ->setCity($data['city'])
            ->setElectors(
                $this->electorRepository->findByAddressData(
                    [
                        'number' => $data['number'],
                        'street' => $data['street'],
                        'city' => $data['city']
                    ]
                )->execute()
            );

        return $address;
    }

    public function checkData(array $data): bool
    {
        if (!$data['number'] || !$data['street'] || !$data['city']) {
            return false;
        }

        return true;
    }
}
