<?php

namespace SlackOrder\Service;

use SlackOrder\Entity\Order;
use SlackOrder\Repository\OrderRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Translation\Translator;

class OrderCommandService {

    /** @var EntityManager $em */
    private $em;

    /** @var \Swift_Mailer */
    private $mailer;

    /** @var \Twig_Environment  */
    private $twig;

    /** @var  Translator */
    private $translator;

    /** @var  String */
    private $orderCommandName;

    /** @var  String */
    private $orderRestaurantName;

    /** @var  String */
    private $orderRestaurantPhoneNumber;

    /** @var  String */
    private $orderRestaurantMenuUrl;

    /** @var  String */
    private $orderStartHour;

    /** @var  String */
    private $orderEndHour;

    /** @var  String */
    private $orderExample;

    /** @var  String */
    private $orderSenderEmail;

    public function __construct(
        EntityManager $entityManager, \Swift_Mailer $mailer, \Twig_Environment $twig, Translator $translator, array $orderConfig)
    {
        $this->em = $entityManager;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->translator = $translator;

        $this->orderCommandName = $orderConfig['name'];
        $this->orderRestaurantName = $orderConfig['restaurant']['name'];
        $this->orderRestaurantPhoneNumber = $orderConfig['restaurant']['phone_number'];
        $this->orderRestaurantEmail = $orderConfig['restaurant']['email'];
        $this->orderRestaurantMenuUrl = $orderConfig['restaurant']['menu_url'];
        $this->orderStartHour = $orderConfig['start_hour'];
        $this->orderEndHour = $orderConfig['end_hour'];
        $this->orderSendByMailActivated = $orderConfig['send_by_mail'];
        $this->orderExample = $orderConfig['example'];
        $this->orderSenderEmail = $orderConfig['sender_email'];
    }

    /**
     * @param $name
     * @param $order
     * @return array
     */
    public function addOrder($name, $order)
    {
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->em->getRepository('SlackOrder\Entity\Order');

        if (!$this->inTime()) {

            return [
                'text' => $this->translator->trans('order.sorry.notInTime',
                    ['%orderStartHour%' => $this->orderStartHour,'%orderEndHour%' => $this->orderEndHour]),
                'attachments' => [
                    [
                        'fallback' => 'Fail ?',
                        'text' => $this->translator->trans('order.sorry.notInTimePlanB',
                            ['%restaurantName%' => $this->orderRestaurantName, '%restaurantPhoneNumber%' => $this->orderRestaurantPhoneNumber]),
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
            'text' => $this->translator->trans('order.success',
                ['%name%' => $name, '%commandName%' => $this->orderCommandName]),
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
        $orderRepository = $this->em->getRepository('SlackOrder\Entity\Order');

        if (!$this->inTime()) {
            return [
                'text' => $this->translator->trans('order.cancel.tooLate'),
                'attachments' => [
                    [
                        'fallback' => 'Fail ?',
                        'text' => $this->translator->trans('order.cancel.tooLatePlanB',
                            ['%restaurantName%' => $this->orderRestaurantName, '%restaurantPhoneNumber%' => $this->orderRestaurantPhoneNumber]),
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
                'text' => $this->translator->trans('order.cancel.noOrder'),
            ];
        }

        $this->em->remove($orderEntity);
        $this->em->flush();

        return [
            'text' => $this->translator->trans('order.cancel.success'),
        ];
    }

    /**
     * @return array
     */
    public function orderList()
    {
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->em->getRepository('SlackOrder\Entity\Order');

        $date = new \DateTime();
        $date->setTime(0, 0, 0);
        /** @var Order[] $orders */
        $orders = $orderRepository->findBy(['date' => $date]);

        if (count($orders) === 0) {
            return [
                'text' => $this->translator->trans('order.list.noOrderPlaced'),
            ];
        }

        $attachments = [];

        foreach ($orders as $order) {
            $attachment = [
                'fallback' => 'Fail ?',
                'text' => $this->translator->trans('order.list.detail',
                    ['%name%' => $order->getName(), '%orderDetail%' => $order->getOrder()]),
            ];

            $attachments[] = $attachment;
        }

        return [
            'text' => $this->translator->trans('order.list.title'),
            'mrkdwn' => true,
            'attachments' => $attachments
        ];
    }

    /**
     * @return array
     */
    public function menu()
    {
        if (null === $this->orderRestaurantMenuUrl) {
            return [
                'text' => $this->translator->trans('order.menu.noMenu'),
            ];
        }

        return [
            'text' => $this->translator->trans('order.menu.title'),
            'mrkdwn' => true,
            'attachments' => [
                [
                    'fallback' => 'Fail ?',
                    'title_link' => $this->orderRestaurantMenuUrl,
                ],
            ],
        ];
    }

    /**
     * @param string $name
     * @param array $params
     * @return array
     */
    public function send($name, $params)
    {
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->em->getRepository('SlackOrder\Entity\Order');

        try {
            $orderEntity = $orderRepository->getFirstOrderNotSentToday();
        }catch (NoResultException $e) {
            return [
                'text' => $this->translator->trans('order.send.noOrderPlacedToday'),
                'mrkdwn' => true,
            ];
        }

        if ($this->inTime()) {
            return [
                'text' => $this->translator->trans('order.send.toEarly', ['%orderEndHour%' => $this->orderEndHour]),
                'mrkdwn' => true,
            ];
        }

        if ($orderEntity->getName() !== $name) {
            return [
                'text' => $this->translator->trans('order.send.notAuthorizedToSendOrders', ['%name%' => $orderEntity->getName()]),
                'mrkdwn' => true,
            ];
        }

        $hour = $params[1];
        if(!preg_match('/^[0-9]{2}:[0-9]{2}$/', $hour)) {
            return [
                'text' => $this->translator->trans('order.send.invalidHourFormat'),
                'mrkdwn' => true,
            ];
        }

        unset($params[1]);
        $phoneNumber = implode('', $params);

        if(!preg_match('/^[0-9]{10}$/', $phoneNumber)) {
            return [
                'text' => $this->translator->trans('order.send.invalidPhoneNumberFormat'),
                'mrkdwn' => true,
            ];
        }

        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->em->getRepository('SlackOrder\Entity\Order');

        $date = new \DateTime();
        $date->setTime(0, 0, 0);
        $orders = $orderRepository->findBy(['date' => $date, 'sent' => false]);

        if ($this->orderSendByMailActivated == false) {
            $orderRepository->setOrderAsSent();

            return [
                'text' => $this->translator->trans('order.send.orderByEmailNotActivated',
                    ['%restaurantPhoneNumber%' => $this->orderRestaurantPhoneNumber]),
            ];
        }

        if ($this->sendEmail($hour, $phoneNumber, $name, $orders) === 0) {

            return [
                'text' => $this->translator->trans('order.send.fail',
                    ['%restaurantPhoneNumber%' => $this->orderRestaurantPhoneNumber]),
            ];
        }

        $orderRepository->setOrderAsSent();

        return [
            'text' => $this->translator->trans('order.send.success'),
            'mrkdwn' => true,
            'attachments' => [
                [
                    'fallback' => 'Fail ?',
                    'text' => $this->translator->trans('order.send.successConfirm',
                        ['%name%' => ucfirst($orderEntity->getName()), '%restaurantPhoneNumber%' => $this->orderRestaurantPhoneNumber]),
                    'color' => 'danger',
                ],
            ],
        ];
    }

    /**
     * @param $name
     * @return array
     */
    public function historyList($name)
    {
        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->em->getRepository('SlackOrder\Entity\Order');

        /** @var Order[] $orders */
        $orders = $orderRepository->findBy(['name' => $name, 'sent' => true]);

        if (count($orders) === 0) {
            return [
                'text' => $this->translator->trans('order.history.never'),
            ];
        }

        $attachments = [];

        $dateFormat = $this->translator->trans('order.history.dateFormat');

        foreach ($orders as $order) {
            $attachment = [
                'fallback' => 'Fail ?',
                'text' => $this->translator->trans('order.history.detail',
                    ['%orderDetail%' => $order->getOrder(), '%orderDate%' => $order->getDate()->format($dateFormat)]),
            ];

            $attachments[] = $attachment;
        }

        return [
            'text' => $this->translator->trans('order.history.title'),
            'mrkdwn' => true,
            'attachments' => $attachments
        ];
    }

    /**
     * @return array
     */
    public function help()
    {
        $text = $this->translator->trans('order.help',
            [
                '%commandName%' => $this->orderCommandName,
                '%orderExample%' => $this->orderExample,
                '%restaurantName%' => $this->orderRestaurantName,
                '%optionPlace%' => $this->translator->trans("command.options.place"),
                '%optionCancel%' => $this->translator->trans("command.options.cancel"),
                '%optionList%' => $this->translator->trans("command.options.list"),
                '%optionSend%' => $this->translator->trans("command.options.send"),
                '%optionHistory%' => $this->translator->trans("command.options.history"),
            ]
        );

        return [
            'text' => $text,
            'mrkdwn' => true,
            'attachments' => [
                [
                    'fallback' => 'Fail ?',
                    'text' => $this->translator->trans('order.helpDanger', ['%endHourOrderAllow%' => $this->orderEndHour]),
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
            throw new \InvalidArgumentException('Invalid order start hour format in config.yml');
        }
        if(!preg_match('/^[0-9]{2}:[0-9]{2}$/', $this->orderEndHour)) {
            throw new \InvalidArgumentException('Invalid order end hour format in config.yml');
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
     * @param String $hour
     * @param String $phoneNumber
     * @param String $name
     * @param Order[] $orders
     * @return int
     */
    private function sendEmail($hour, $phoneNumber, $name, $orders)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($this->translator->trans('email.subject'))
            ->setFrom($this->orderSenderEmail)
            ->setTo($this->orderRestaurantEmail)
            ->setBody(
                $this->twig->render(
                    'Emails/order.html.twig',
                    [
                        'name' => $name,
                        'hour' => $hour,
                        'phoneNumber' => $phoneNumber,
                        'orders' => $orders,
                    ]
                ),
                'text/html'
            );

        return $this->mailer->send($message);
    }
}
