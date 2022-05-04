<?php

namespace App\Repository;

use App\Entity\GroupedAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GroupedAddress|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupedAddress|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupedAddress[]    findAll()
 * @method GroupedAddress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupedAddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupedAddress::class);
    }

    public function findStreets(string $city)
    {
        return $this->createQueryBuilder('a')
            ->select('a.street')
            ->join('a.electors', 'e')
            ->andWhere('a.city = :city')
            ->groupBy('a.street')
            ->orderBy('a.street', 'asc')
            ->setParameter('city', $city)
            ->getQuery();
    }

    public function findByStreet(string $street, string $city)
    {
        return $this->createQueryBuilder('a')
            ->where('a.street = :street')
            ->andWhere('a.city = :city')
            ->setParameter('street', $street)
            ->setParameter('city', $city)
            ->addSelect('ABS(a.number) AS HIDDEN intNumber')
            ->orderBy('intNumber')
            ->getQuery();

    }
}
