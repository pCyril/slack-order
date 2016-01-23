<?php

namespace SlackOrder\Repository;

use SlackOrder\Entity\Order;
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
    public function getFirstOrderNotSentToday()
    {
        $date = new \DateTime();
        $date->setTime(0, 0, 0);
        $qb = $this->createQueryBuilder('o')
            ->select('o')
            ->where('o.date = :date')
            ->andWhere('o.sent = :sent')
            ->setMaxResults(1)
            ->setParameter('date', $date)
            ->setParameter('sent', false);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @return mixed
     */
    public function setOrderAsSent()
    {
        $date = new \DateTime();
        $date->setTime(0, 0, 0);
        $qb = $this->createQueryBuilder('o');
        $query = $qb->update()
            ->set('o.sent', '?1')
            ->where('o.date = :date')
            ->setParameter('1', true)
            ->setParameter('date', $date)
            ->getQuery();

        return $query->execute();
    }
}
