<?php
/**
 * AppExtension.php
 *
 * @author    Tristan Florin <tristan.florin@smile.fr>
 * @copyright 2022 Smile
 */

namespace App\Twig;

use App\Entity\GroupedAddress;
use App\Repository\ElectorRepository;
use App\Repository\GroupedAddressRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private GroupedAddressRepository $groupedAddressRepository;
    private ElectorRepository $electorRepository;

    public function __construct(
        GroupedAddressRepository $groupedAddressRepository,
        ElectorRepository $electorRepository
    ) {
        $this->groupedAddressRepository = $groupedAddressRepository;
        $this->electorRepository = $electorRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('addresses_by_street', [$this, 'getAddressesByStreet']),
            new TwigFunction('get_electors', [$this, 'getElectors']),
            new TwigFunction('vote_office', [$this, 'getVoteOffice']),
            new TwigFunction('age', [$this, 'getAge']),
        ];
    }

    public function getAddressesByStreet(string $street, string $city)
    {
        return $this->groupedAddressRepository->findByStreet($street, $city)->execute();
    }

    public function getElectors(GroupedAddress $address)
    {
        return $this->electorRepository->findByAddressSortedByAppt($address)->execute();
    }

    public function getAge(\DateTime $birthdate)
    {
        return date_diff($birthdate, new \DateTime('now'))->format("%y");
    }
}
