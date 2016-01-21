<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Order;
use Doctrine\ORM\EntityRepository;

/**
 * @method Order find($id)
 * @method Order[] findAll()
 */
class OrderRepository extends EntityRepository
{
    /**
     * @return Order|NULL
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getFirstOrderToday()
    {
        $date = new \DateTime();
        $date->setTime(0, 0, 0);
        $qb = $this->createQueryBuilder('b')
            ->select('b')
            ->where('b.date = :date')
            ->setMaxResults(1)
            ->setParameter('date', $date);

        return $qb->getQuery()->getSingleResult();
    }
}
