<?php

namespace App\Repository;

use App\Entity\AllowedIp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AllowedIp>
 *
 * @method AllowedIp|null find($id, $lockMode = null, $lockVersion = null)
 * @method AllowedIp|null findOneBy(array $criteria, array $orderBy = null)
 * @method AllowedIp[]    findAll()
 * @method AllowedIp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AllowedIpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AllowedIp::class);
    }

    public function createAllowedIp(string $ip): void
    {
        $allowedIp = new AllowedIp();
        $allowedIp->setIp($ip);

        $this->getEntityManager()->persist($allowedIp);
        $this->getEntityManager()->flush();
    }
}
