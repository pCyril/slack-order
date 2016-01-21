<?php

namespace AppBundle\Service;

use AppBundle\Entity\Order;
use AppBundle\Repository\OrderRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OrderCommandService {

    const ORDER_COMMAND_LIST = 'liste';

    const ORDER_COMMAND_ORDER = 'commande';

    const ORDER_COMMAND_CANCEL = 'annuler';

    const ORDER_COMMAND_HELP = 'help';

    const ORDER_COMMAND_RANDOM = 'aléatoire';

    const ORDER_COMMAND_SEND = 'envoyer';

    /** @var EntityManager $em */
    private $em;

    /** @var ContainerInterface $container */
    private $container;

    /** @var \Swift_Mailer */
    private $mailer;

    private $twig;

    /** @var  String */
    private $orderCommandName;

    /** @var  String */
    private $orderRestaurantName;

    /** @var  String */
    private $orderRestaurantPhoneNumber;

    /** @var  String */
    private $orderStartHour;

    /** @var  String */
    private $orderEndHour;

    /** @var  String */
    private $orderExample;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
        $this->mailer = $container->get('mailer');
        $this->twig = $container->get('twig');

        $this->orderCommandName = $container->getParameter('order_command_name');
        $this->orderRestaurantName = $container->getParameter('order_restaurant_name');
        $this->orderRestaurantPhoneNumber = $container->getParameter('order_restaurant_phone_number');
        $this->orderStartHour = $container->getParameter('order_start_hour');
        $this->orderEndHour = $container->getParameter('order_end_hour');
        $this->orderSendByMailActivated = $container->getParameter('order_send_by_mail_activate');
        $this->orderExample = $container->getParameter('order_example');
    }

    /**
     * @param $name
     * @param $order
     * @return array
     */
    public function addOrder($name, $order)
    {
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->em->getRepository('AppBundle:Order');

        if (!($this->inTime())) {

            return [
                'text' => sprintf('Désolé les commandes ne sont accéptés que de %s à %s', $this->orderStartHour, $this->orderEndHour),
                'attachments' => [
                    [
                        'fallback' => 'Fail ?',
                        'text' => sprintf('Tu peux quand même appeler %s au %s', $this->orderRestaurantName, $this->orderRestaurantPhoneNumber),
                    ],
                ],
            ];
        }

        $date = new \DateTime();
        $date->setTime(0, 0, 0);
        $orderEntity = $orderRepository->findOneBy(['name' => $name, 'date' => $date]);

        $orderEntity = $orderEntity ? $orderEntity : new Order();

        $orderEntity
            ->setName($name)
            ->setDate($date)
            ->setOrder($order);

        $this->em->persist($orderEntity);
        $this->em->flush();

        return [
            'response_type' => 'in_channel',
            'text' => sprintf('%s a rejoint la commande groupé à midi si tu souhaites en faire de même utilise la commande `%s`', $name, $this->orderCommandName),
            'mrkdwn' => true,
        ];
    }

    /**
     * @param $name
     * @return array
     */
    public function cancelOrder($name)
    {
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->em->getRepository('AppBundle:Order');

        if (!($this->inTime())) {
            return [
                'text' => 'Il est trop tard pour annuler ta commande.',
                'attachments' => [
                    [
                        'fallback' => 'Fail ?',
                        'text' => sprintf('Tu peux quand même essayer d\'appeler %s au %s', $this->orderRestaurantName, $this->orderRestaurantPhoneNumber),
                        'color' => 'danger',
                    ],
                ],
            ];
        }

        $date = new \DateTime();
        $date->setTime(0, 0, 0);
        $orderEntity = $orderRepository->findOneBy(['name' => $name, 'date' => $date]);
        if (null === $orderEntity) {
            return [
                'text' => 'Tu n\'avais rien commandé, mais dans le doute tu as bien fait.',
            ];
        }

        $this->em->remove($orderEntity);
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
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->em->getRepository('AppBundle:Order');

        $date = new \DateTime();
        $date->setTime(0, 0, 0);
        /** @var Order[] $orders */
        $orders = $orderRepository->findBy(['date' => $date]);

        if (count($orders) === 0) {
            return [
                'text' => 'Personne n\'a commandé aujourd\'hui',
            ];
        }

        $attachments = [];

        foreach ($orders as $order) {
            $attachment = [
                'fallback' => sprintf('%s a commandé : %s', $order->getName(), $order->getOrder()),
                'text' => sprintf('%s a commandé : %s', $order->getName(), $order->getOrder()),
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
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->em->getRepository('AppBundle:Order');

        try {
            $orderEntity = $orderRepository->getFirstOrderToday();
        }catch (NoResultException $e) {
            return [
                'text' => '*Il n\'y a pas eu de commande aujourd\'hui.*',
            ];
        }

        if ($orderEntity->getName() !== $name) {
            return [
                'text' => sprintf('*Ce n\'est pas toi qui a initié la commande, demande à %s si il peut envoyer la commande.*', $orderEntity->getName()),
            ];
        }

        if ($this->orderSendByMailActivated == false) {
            return [
                'text' => sprintf('*L\'envoie de la commande par email n\'est pas activé merci de passer la commande par téléphone au %s.*', $this->orderRestaurantPhoneNumber),
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
                'text' => sprintf('*L\'email n\'a pas été envoyé merci de passer la commande par téléphone au %s.*', $this->orderRestaurantPhoneNumber),
            ];
        }

        return [
            'text' => '*La commande a été envoyée*',
            'attachments' => [
                [
                    'fallback' => 'Fail ?',
                    'text' => sprintf('%s, tu peux quand même confirmer par téléphone au %s ?', ucfirst($orderEntity->getName()), $this->orderRestaurantPhoneNumber),
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
                - Si tu souhaites passer ou modifier une commande. `'.$this->orderCommandName.' commande '.$this->orderExample.'`
                - Si tu n\'as plus faim. `'.$this->orderCommandName.' annuler`
                - Tu souhaites savoir avec qui tu vas manger ? `'.$this->orderCommandName.' liste`
                - Tu ne sais pas quoi choisir ? `'.$this->orderCommandName.' aléatoire`
                - Tu veux envoyer la commande à '.$this->orderRestaurantName.' ? `'.$this->orderCommandName.' envoyer hh:mm 06********`',
            'mrkdwn' => true,
            'attachments' => [
                [
                    'fallback' => 'Fail ?',
                    'text' => sprintf('Important: Tu as jusqu\'à %s pour passer ta commande.', $this->orderEndHour),
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
        if(!preg_match('/^[0-9]{2}:[0-9]{2}$/', $this->orderStartHour)) {
            throw new \InvalidArgumentException('Le paramètre de order_star_hour est invalide merci d\'utiliser le format 08:00');
        }
        if(!preg_match('/^[0-9]{2}:[0-9]{2}$/', $this->orderEndHour)) {
            throw new \InvalidArgumentException('Le paramètre de order_end_hour est invalide merci d\'utiliser le format 08:00');
        }
        $currentDate = new \DateTime();
        $startDate = new \DateTime();
        $orderStartHourExploded = explode(':', $this->orderStartHour);
        $startDate->setTime(intval($orderStartHourExploded[0]), intval($orderStartHourExploded[1]), 0);
        $endDate = new \DateTime();
        $orderEndHourExploded = explode(':', $this->orderEndHour);
        $endDate->setTime(intval($orderEndHourExploded[0]), intval($orderEndHourExploded[1]), 0);

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
            ->setSubject('Commande')
            ->setFrom('send@example.com')
            ->setTo($this->container->getParameter('order_restaurant_email'))
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
