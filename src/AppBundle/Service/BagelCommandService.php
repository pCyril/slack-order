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

        $currentDate = new \DateTime();
        $startDate = new \DateTime();
        $startDate->setTime(8, 0, 0);
        $endDate = new \DateTime();
        $endDate->setTime(11, 10, 0);
        if ($currentDate >= $startDate && $currentDate <= $endDate) {
            $currentDate->setTime(0, 0, 0);
            $bagel = $bagelRepository->findOneBy(['name' => $name, 'date' => $currentDate]);

            $bagel = $bagel ? $bagel : new Bagel();

            $bagel
                ->setName($name)
                ->setDate($currentDate)
                ->setOrder($order);

            $this->em->persist($bagel);
            $this->em->flush();

            return [
                'response_type' => 'in_channel',
                'text' => sprintf('%s a commandé un bagel pour ce midi.', $name),
                'attachments' => [
                    [
                        'fallback' => 'Fail ?',
                        'text' => sprintf('Il devrait manger à midi un `%s`, bon appétit!', $order),
                        'mrkdwn' => true,
                    ],
                    [
                        'fallback' => 'Fail ?',
                        'text' => 'Tu souhaites toi aussi manger un bagel ? Utilise la commande `/bagel`',
                        'mrkdwn' => true,
                    ],
                ],
            ];
        } else {

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
    }

    /**
     * @param $name
     * @return array
     */
    public function cancelOrder($name)
    {
        /** @var BagelRepository $bagelRepository */
        $bagelRepository = $this->em->getRepository('AppBundle:Bagel');

        $currentDate = new \DateTime();
        $startDate = new \DateTime();
        $startDate->setTime(8, 0, 0);
        $endDate = new \DateTime();
        $endDate->setTime(11, 10, 0);
        if ($currentDate >= $startDate && $currentDate <= $endDate) {
            $currentDate->setTime(0, 0, 0);
            $bagel = $bagelRepository->findOneBy(['name' => $name, 'date' => $currentDate]);
            if (null === $bagel) {
                return [
                    'text' => 'Tu n\avais rien commandé, mais dans le doute tu as bien fait.',
                ];
            }

            $this->em->remove($bagel);
            $this->em->flush();

            return [
                'text' => 'Ta commande a bien été annulée.',
            ];
        } else {
            return [
                'text' => 'Il est trop tard pour annuler ta commande.',
                'attachments' => [
                    [
                        'fallback' => 'Fail ?',
                        'text' => 'Tu peux quand même essayer d\'appeler Bagel Time au 04 78 43 52 19',
                    ],
                ],
            ];
        }
    }

    /**
     * @return array
     */
    public function orderList()
    {
        /** @var BagelRepository $bagelRepository */
        $bagelRepository = $this->em->getRepository('AppBundle:Bagel');

        $currentDate = new \DateTime();
        $currentDate->setTime(0, 0, 0);
        $bagels = $bagelRepository->findBy(['date' => $currentDate]);

        return [];
    }

    /**
     * @return array
     */
    public function help()
    {
        return [
            'text' => 'Tu as faim mais tu ne sais pas comment faire ?',
            'attachments' => [
                [
                    'fallback' => 'Fail ?',
                    'text' => 'Si tu souhaites passer ou modifier une commande.\n `/bagel commande Grenoblois/Pavot/Tartare`',
                    'mrkdwn' => true,
                ],
                [
                    'fallback' => 'Fail ?',
                    'text' => 'Si tu n\'as plus faim.\n `/bagel annuler`',
                    'mrkdwn' => true,
                ],
                [
                    'fallback' => 'Fail ?',
                    'text' => 'Tu souhaites savoir avec qui tu vas manger ?.\n `/bagel liste`',
                    'mrkdwn' => true,
                ],
                [
                    'fallback' => 'Fail ?',
                    'text' => 'Important: Tu as jusqu\'à 11h10 pour passer ta commande.',
                    'color' => 'danger',
                ],
            ],
        ];
    }
}
