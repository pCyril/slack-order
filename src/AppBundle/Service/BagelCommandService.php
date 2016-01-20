<?php

namespace AppBundle\Service;


use AppBundle\Repository\BagelRepository;
use Doctrine\ORM\EntityManager;

class BagelCommandService {

    const BAGEL_COMMAND_LIST = 'liste';

    const BAGEL_COMMAND_ORDER = 'commande';

    const BAGEL_COMMAND_CANCEL = 'annuler';

    const BAGEL_COMMAND_HELP = 'help';

    /** @var EntityManager $em */
    private $em;

    public function __construct($entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param $name
     * @param $order
     * @return array
     */
    public function addOrder($name, $order)
    {
        /** @var BagelRepository $bagelRepository */
        $bagelRepository = $this->em->getRepository('AppBundle:Bagel');

        return [];
    }

    /**
     * @param $name
     * @return array
     */
    public function cancelOrder($name)
    {
        /** @var BagelRepository $bagelRepository */
        $bagelRepository = $this->em->getRepository('AppBundle:Bagel');

        return [];
    }

    /**
     * @return array
     */
    public function orderList()
    {
        /** @var BagelRepository $bagelRepository */
        $bagelRepository = $this->em->getRepository('AppBundle:Bagel');

        return [];
    }

    /**
     * @return array
     */
    public function help()
    {
        return [];
    }
}
