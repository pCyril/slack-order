<?php

namespace SlackOrder\Repository;

use SlackOrder\Entity\Order;
use Doctrine\ORM\EntityRepository;
use SlackOrder\Entity\Restaurant;

/**
 * @method Order find($id)
 * @method Order[] findAll()
 */
class OrderRepository extends EntityRepository
{
    /**
     * @param Restaurant $restaurant
     * @return Order|Null
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getFirstOrderNotSentToday(Restaurant $restaurant)
    {
        $date = new \DateTime();
        $date->setTime(0, 0, 0);
        $qb = $this->createQueryBuilder('o')
            ->select('o')
            ->leftJoin('o.restaurant', 'r')
            ->where('o.date = :date')
            ->andWhere('o.sent = :sent')
            ->andWhere('r.id = :restaurantId')
            ->setMaxResults(1)
            ->setParameter('date', $date)
            ->setParameter('restaurantId', $restaurant->getId())
            ->setParameter('sent', false);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @param Restaurant $restaurant
     * @return mixed
     */
    public function setOrderAsSent(Restaurant $restaurant)
    {
        $date = new \DateTime();
        $date->setTime(0, 0, 0);
        $qb = $this->createQueryBuilder('o');
        $query = $qb->update()
            ->set('o.sent', '?1')
            ->where('o.date = :date')
            ->andWhere('o.restaurant = :restaurantId')
            ->setParameter('1', true)
            ->setParameter('date', $date)
            ->setParameter('restaurantId', $restaurant->getId())
            ->getQuery();

        return $query->execute();
    }
}
