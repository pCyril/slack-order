<?php

namespace SlackOrder\Repository;

use SlackOrder\Entity\Order;
use Doctrine\ORM\EntityRepository;
use SlackOrder\Entity\Restaurant;

/**
 * @method Restaurant find($id)
 */
class RestaurantRepository extends EntityRepository
{
}
