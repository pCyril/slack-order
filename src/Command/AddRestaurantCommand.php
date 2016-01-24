<?php
namespace SlackOrder\Command;

use SlackOrder\Entity\Restaurant;
use SlackOrder\Repository\RestaurantRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class AddRestaurantCommand extends RestaurantCommand
{

    protected function configure()
    {
        $this
            ->setName('order:restaurant:create')
            ->setDescription('This command provide you an interface to create a new restaurant entry.')
            ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $commandNameQuestion = new Question('What is the name of your command ? [/bagel] : ', '/bagel');
        $commandName = $helper->ask($input, $output, $commandNameQuestion);

        /** @var RestaurantRepository $restaurantRepository */
        $restaurantRepository = $this->entityManager->getRepository('SlackOrder\Entity\Restaurant');
        $restaurant = $restaurantRepository->findOneBy(['command' => $commandName]);

        if ($restaurant) {
            throw new \InvalidArgumentException('This command already exists.');
        }

        $restaurant = new Restaurant();
        $restaurant->setCommand($commandName);

        $restaurantNameQuestion = new Question('What is the name of the restaurant ? [our favorite restaurant] : ', 'our favorite restaurant');
        $restaurantName = $helper->ask($input, $output, $restaurantNameQuestion);
        $restaurant->setName($restaurantName);

        $restaurantOrderExampleQuestion = new Question('Can you give an example of order [Big Mac] : ');
        $restaurantOrderExample = $helper->ask($input, $output, $restaurantOrderExampleQuestion);
        $restaurant->setExample($restaurantOrderExample);

        $restaurantPhoneNumberQuestion = new Question('What is the phone number of the restaurant ? [0611223344] : ');
        $restaurantPhoneNumber = $helper->ask($input, $output, $restaurantPhoneNumberQuestion);
        $restaurant->setPhoneNumber($restaurantPhoneNumber);

        $restaurantEmailQuestion = new Question('What is the email of the restaurant ? ');
        $restaurantEmail = $helper->ask($input, $output, $restaurantEmailQuestion);
        if (null !== $restaurantEmail) {
            $restaurant->setSendOrderByEmail(true);
            $restaurantEmailSenderQuestion = new Question('So who will send the mail ? [contact@yourentreprise.com] : ');
            $restaurantEmailSender = $helper->ask($input, $output, $restaurantEmailSenderQuestion);
            $restaurant->setSenderEmail($restaurantEmailSender);
        }

        $restaurantMenuUrlQuestion = new Question('Where can we find the menu on the Internet ? ');
        $restaurantMenuUrl = $helper->ask($input, $output, $restaurantMenuUrlQuestion);
        $restaurant->setUrlMenu($restaurantMenuUrl);

        $orderStartHourQuestion = new Question('When can we start to place an order ? [08:00] : ', '08:00');
        $orderStartHour = $helper->ask($input, $output, $orderStartHourQuestion);
        $restaurant->setStartHour($orderStartHour);

        $orderEndHourQuestion = new Question('When can we send the orders ? [11:00] : ', '11:00');
        $orderEndHour = $helper->ask($input, $output, $orderEndHourQuestion);
        $restaurant->setEndHour($orderEndHour);

        $commandSlackTokenQuestion = new Question('The token given by Slack when you configure it : ');
        $commandSlackToken = $helper->ask($input, $output, $commandSlackTokenQuestion);
        $restaurant->setToken($commandSlackToken);

        $this->validateRestaurant($restaurant);
        $this->entityManager->persist($restaurant);
        $this->entityManager->flush();
    }
}
