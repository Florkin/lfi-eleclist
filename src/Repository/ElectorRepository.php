<?php

namespace App\Repository;

use App\Entity\Elector;
use App\Entity\GroupedAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Elector|null find($id, $lockMode = null, $lockVersion = null)
 * @method Elector|null findOneBy(array $criteria, array $orderBy = null)
 * @method Elector[]    findAll()
 * @method Elector[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ElectorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Elector::class);
    }

    public function getVoteOfficesByCity(string $city): Query
    {
        return $this->createQueryBuilder('e')
            ->select('e.vote_office')
            ->join('e.address', 'a')
            ->where('a.city = :city')
            ->setParameter('city', $city)
            ->groupBy('e.vote_office')
            ->getQuery();
    }

    public function findByAddressData(array $addressData): Query
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->join('e.address', 'a');

        foreach ($addressData as $key => $data) {
            $queryBuilder->andWhere("a." . $key . " = '" . $data . "'");
        }

        return $queryBuilder->getQuery();
    }

    public function findByAddressSortedByAppt(GroupedAddress $address): Query
    {
        return $this->createQueryBuilder('e')
            ->where('e.groupedAddress = :address')
            ->join('e.address', 'a')
            ->addOrderBy('a.add1', 'asc')
            ->setParameter('address', $address)
            ->getQuery();
    }
}
