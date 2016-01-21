<?php

namespace AppBundle\Service;


use AppBundle\Entity\Bagel;
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

        if (!($this->inTime())) {

            return [
                'text' => 'Désolé les commandes ne sont accéptés que de 8:00 à 11:10',
                'attachments' => [
                    [
                        'fallback' => 'Fail ?',
                        'text' => 'Tu peux quand même appeler Bagel Time au 04 78 43 52 19',
                    ],
                ],
            ];
        }

        $date = new \DateTime();
        $date->setTime(0, 0, 0);
        $bagel = $bagelRepository->findOneBy(['name' => $name, 'date' => $date]);

        $bagel = $bagel ? $bagel : new Bagel();

        $bagel
            ->setName($name)
            ->setDate($date)
            ->setOrder($order);

        $this->em->persist($bagel);
        $this->em->flush();

        return [
            'response_type' => 'in_channel',
            'text' => sprintf('%s a décidé de manger un bagel à midi si tu souhaites le rejoindre utilise la commande `/bagel`', $name),
            'mrkdwn' => true,
        ];
    }

    /**
     * @param $name
     * @return array
     */
    public function cancelOrder($name)
    {
        /** @var BagelRepository $bagelRepository */
        $bagelRepository = $this->em->getRepository('AppBundle:Bagel');

        if (!($this->inTime())) {
            return [
                'text' => 'Il est trop tard pour annuler ta commande.',
                'attachments' => [
                    [
                        'fallback' => 'Fail ?',
                        'text' => 'Tu peux quand même essayer d\'appeler Bagel Time au 04 78 43 52 19',
                        'color' => 'danger',
                    ],
                ],
            ];
        }

        $date = new \DateTime();
        $date->setTime(0, 0, 0);
        $bagel = $bagelRepository->findOneBy(['name' => $name, 'date' => $date]);
        if (null === $bagel) {
            return [
                'text' => 'Tu n\'avais rien commandé, mais dans le doute tu as bien fait.',
            ];
        }

        $this->em->remove($bagel);
        $this->em->flush();

        return [
            'text' => 'Ta commande a bien été annulée.',
        ];
    }

    /**
     * @return array
     */
    public function orderList()
    {
        /** @var BagelRepository $bagelRepository */
        $bagelRepository = $this->em->getRepository('AppBundle:Bagel');

        $date = new \DateTime();
        $date->setTime(0, 0, 0);
        /** @var Bagel[] $bagels */
        $bagels = $bagelRepository->findBy(['date' => $date]);

        if (count($bagels) === 0) {
            return [
                'text' => 'Personne n\'a commandé de bagel aujourd\'hui',
            ];
        }

        $attachments = [];

        foreach ($bagels as $bagel) {
            $attachment = [
                'fallback' => sprintf('%s a commandé : %s', $bagel->getName(), $bagel->getOrder()),
                'text' => sprintf('%s a commandé : %s', $bagel->getName(), $bagel->getOrder()),
            ];

            $attachments[] = $attachment;
        }

        return [
            'text' => '*Voici les personnes avec qui tu vas manger :*',
            'mrkdwn' => true,
            'attachments' => $attachments
        ];
    }

    /**
     * @return array
     */
    public function help()
    {
        return [
            'text' => '*Tu as faim mais tu ne sais pas comment faire ?*
                - Si tu souhaites passer ou modifier une commande. `/bagel commande Grenoblois/Pavot/Tartare`
                - Si tu n\'as plus faim. `/bagel annuler`
                - Tu souhaites savoir avec qui tu vas manger ?. `/bagel liste`',

            'mrkdwn' => true,
            'attachments' => [
                [
                    'fallback' => 'Fail ?',
                    'text' => 'Important: Tu as jusqu\'à 11h10 pour passer ta commande.',
                    'color' => 'danger',
                ],
            ],
        ];
    }

    /**
     * @return bool
     */
    private function inTime()
    {
        $currentDate = new \DateTime();
        $startDate = new \DateTime();
        $startDate->setTime(8, 0, 0);
        $endDate = new \DateTime();
        $endDate->setTime(11, 10, 0);

        return ($currentDate >= $startDate && $currentDate <= $endDate);
    }
}
