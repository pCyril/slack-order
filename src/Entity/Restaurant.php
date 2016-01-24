<?php

namespace SlackOrder\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="restaurant")
 * @ORM\Entity(repositoryClass="SlackOrder\Repository\RestaurantRepository")
 */
class Restaurant
{

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $command;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    private $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="text", name="url_menu", nullable=true)
     */
    private $urlMenu;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="start_hour", length=5, nullable=false)
     */
    private $startHour;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="end_hour", length=5, nullable=false)
     */
    private $endHour;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="send_order_by_email", length=5, nullable=false, options={"default": false})
     */
    private $sendOrderByEmail;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="sender_email", nullable=true)
     */
    private $senderEmail;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private $example;

    /**
     * @OneToMany(targetEntity="Order", mappedBy="restaurant")
     */
    private $orders;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param $command
     * @return $this
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param $phoneNumber
     * @return $this
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlMenu()
    {
        return $this->urlMenu;
    }

    /**
     * @param $urlMenu
     * @return $this
     */
    public function setUrlMenu($urlMenu)
    {
        $this->urlMenu = $urlMenu;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndHour()
    {
        return $this->endHour;
    }

    /**
     * @param $endHour
     * @return $this
     */
    public function setEndHour($endHour)
    {
        $this->endHour = $endHour;

        return $this;
    }

    /**
     * @return string
     */
    public function getStartHour()
    {
        return $this->startHour;
    }

    /**
     * @param $startHour
     * @return $this
     */
    public function setStartHour($startHour)
    {
        $this->startHour = $startHour;

        return $this;
    }

    /**
     * @return bool
     */
    public function sendOrderByEmail()
    {
        return $this->sendOrderByEmail;
    }

    /**
     * @param $sendOrderByEmail
     * @return $this
     */
    public function setSendOrderByEmail($sendOrderByEmail)
    {
        $this->sendOrderByEmail = $sendOrderByEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getSenderEmail()
    {
        return $this->senderEmail;
    }

    /**
     * @param $email
     * @return $this
     */
    public function setSenderEmail($email)
    {
        $this->senderEmail = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getExample()
    {
        return $this->example;
    }

    /**
     * @param $example
     * @return $this
     */
    public function setExample($example)
    {
        $this->example = $example;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getOrders()
    {
        return $this->orders;
    }
}
