<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Bagel;
use Doctrine\ORM\EntityRepository;

/**
 * @method Bagel find($id)
 * @method Bagel[] findAll()
 */
class BagelRepository extends EntityRepository
{
    /**
     * @return Bagel|NULL
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
