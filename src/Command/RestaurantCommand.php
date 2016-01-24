<?php
namespace SlackOrder\Command;

use Doctrine\ORM\EntityManager;
use SlackOrder\Entity\Restaurant;
use Symfony\Component\Console\Command\Command;

class RestaurantCommand extends Command
{
    /** @var  EntityManager */
    protected  $entityManager;

    public function __construct(EntityManager $entityManager, $name = null)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
    }

    /**
     * @param Restaurant $restaurant
     * @return bool
     */
    public function validateRestaurant(Restaurant $restaurant)
    {
        if (null === $restaurant->getCommand()) {
            throw new \InvalidArgumentException('The command can\'t be null.');
        }

        if(!preg_match('/^\/([a-z]+)$/', $restaurant->getCommand())) {
            throw new \InvalidArgumentException('Invalid command name format: /bagel, only letters are allowed and must start by /');
        }

        if(mb_strlen($restaurant->getName()) > 255) {
            throw new \InvalidArgumentException('The restaurant name must be less than 255 characters.');
        }

        if (null === $restaurant->getExample()) {
            throw new \InvalidArgumentException('The order example can\'t be null.');
        }

        if (null === $restaurant->getPhoneNumber()) {
            throw new \InvalidArgumentException('The restaurant phone number can\'t be null.');
        }

        if(!preg_match('/^[0-9]{10}$/', $restaurant->getPhoneNumber())) {
            throw new \InvalidArgumentException('Invalid restaurant phone number format: Only numbers are allowed.');
        }

        if(!preg_match('/^[0-9]{2}:[0-9]{2}$/', $restaurant->getStartHour())) {
            throw new \InvalidArgumentException('Invalid start hour format.');
        }

        if(!preg_match('/^[0-9]{2}:[0-9]{2}$/', $restaurant->getEndHour())) {
            throw new \InvalidArgumentException('Invalid end hour format.');
        }

        if(null === $restaurant->getToken()) {
            throw new \InvalidArgumentException('The token can\'t be null.');
        }

        if ($restaurant->sendOrderByEmail() && null === $restaurant->getEndHour()) {
            throw new \InvalidArgumentException('You must set the restaurant email if you want to send order by email');
        }

        if ($restaurant->sendOrderByEmail() && null === $restaurant->getSenderEmail()) {
            throw new \InvalidArgumentException('You must set the sender email if you want to send order by email');
        }

        return true;
    }
}
