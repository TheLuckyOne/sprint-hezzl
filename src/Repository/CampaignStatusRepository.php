<?php

namespace App\Repository;

use App\Entity\CampaignStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CampaignStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method CampaignStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method CampaignStatus[]    findAll()
 * @method CampaignStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CampaignStatusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CampaignStatus::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('c')
            ->where('c.something = :value')->setParameter('value', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
