<?php

namespace AppBundle\Service;


use AppBundle\Entity\Bagel;
use AppBundle\Repository\BagelRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BagelCommandService {

    const BAGEL_COMMAND_LIST = 'liste';

    const BAGEL_COMMAND_ORDER = 'commande';

    const BAGEL_COMMAND_CANCEL = 'annuler';

    const BAGEL_COMMAND_HELP = 'help';

    const BAGEL_COMMAND_RANDOM = 'aléatoire';

    const BAGEL_COMMAND_SEND = 'envoyer';

    /** @var EntityManager $em */
    private $em;

    /** @var ContainerInterface $container */
    private $container;

    /** @var \Swift_Mailer */
    private $mailer;

    /** @var  TwigEngine */
    private $twig;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
        $this->mailer = $container->get('mailer');
        $this->twig = $container->get('twig');
    }

    public function randomOrder($name)
    {
        $bagelParameters = $this->container->getParameter('bagel');

        if (!isset($bagelParameters['breads']) || !isset($bagelParameters['sauces']) || !isset($bagelParameters['bagels'])) {
            throw new \DomainException('Parameters for bagels incompletes or incorrects.');
        }

        $bagel = $bagelParameters['bagels'][array_rand($bagelParameters['bagels'])];
        $bread = $bagelParameters['breads'][array_rand($bagelParameters['breads'])];
        $sauce = $bagelParameters['sauces'][array_rand($bagelParameters['sauces'])];

        $order = sprintf('%s/%s/%s', $bagel, $bread, $sauce);

        return $this->addOrder($name, $order);
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

    public function send($name, $params)
    {
        /** @var BagelRepository $bagelRepository */
        $bagelRepository = $this->em->getRepository('AppBundle:Bagel');

        try {
            $bagel = $bagelRepository->getFirstOrderToday();
        }catch (NoResultException $e) {
            return [
                'text' => '*Il n\'y a pas eu de commande aujourd\'hui.*',
            ];
        }

        if ($bagel->getName() !== $name) {
            return [
                'text' => sprintf('*Ce n\'est pas toi qui a initié la commande, demande à %s si il peut envoyer la commande.*', $bagel->getName()),
            ];
        }

        $hour = $params[0];
        if(!preg_match('/^[0-9]{2}:[0-9]{2}$/', $hour)) {
            return [
                'text' => '*Le format de l\'heure n\'est pas correct*',
            ];
        }

        unset($params[0]);
        $phoneNumber = implode('', $params);

        if(!preg_match('/^[0-9]{10}$/', $hour)) {
            return [
                'text' => '*Le format du numéro de téléphone n\'est pas correct*',
            ];
        }

        if ($this->sendEmail($hour, $phoneNumber) === 0) {
            return [
                'text' => '*L\'email n\'a pas été envoyé merci de passer la commande par téléphone au 04 78 43 52 19.*',
            ];
        }

        return [
            'text' => '*La commande a été envoyée*',
            'attachments' => [
                [
                    'fallback' => 'Fail ?',
                    'text' => sprintf('%s, tu peux quand même confirmer par téléphone au 04 78 43 52 19 ?', ucfirst($bagel->getName())),
                    'color' => 'danger',
                ],
            ],
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
                - Tu souhaites savoir avec qui tu vas manger ? `/bagel liste`
                - Tu ne sais pas quoi choisir ? `/bagel aléatoire`
                - Tu veux envoyer la commande à Bagel Time ? `/bagel envoyer hh:mm 06********`',
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

    /**
     * @param $hour
     * @param $phoneNumber
     * @return int
     */
    private function sendEmail($hour, $phoneNumber)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Commande de bagels')
            ->setFrom('send@example.com')
            ->setTo($this->container->getParameter('bagel_email'))
            ->setBody(
                $this->twig->renderView(
                // app/Resources/views/Emails/registration.html.twig
                    'Emails/registration.html.twig',
                    array('name' => '')
                ),
                'text/html'
            );

        return $this->mailer->send($message);
    }
}
