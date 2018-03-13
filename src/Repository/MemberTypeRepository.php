<?php

namespace App\Repository;

use App\Entity\MemberType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MemberType|null find($id, $lockMode = null, $lockVersion = null)
 * @method MemberType|null findOneBy(array $criteria, array $orderBy = null)
 * @method MemberType[]    findAll()
 * @method MemberType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MemberTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MemberType::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('a')
            ->where('a.something = :value')->setParameter('value', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
