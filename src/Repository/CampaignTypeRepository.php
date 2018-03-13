<?php

namespace App\Repository;

use App\Entity\CampaignType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CampaignType|null find($id, $lockMode = null, $lockVersion = null)
 * @method CampaignType|null findOneBy(array $criteria, array $orderBy = null)
 * @method CampaignType[]    findAll()
 * @method CampaignType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CampaignTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CampaignType::class);
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
