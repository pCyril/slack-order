<?php
namespace SlackOrder\Command;

use SlackOrder\Entity\Restaurant;
use SlackOrder\Repository\RestaurantRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class UpdateRestaurantCommand extends RestaurantCommand
{
    protected function configure()
    {
        $this
            ->setName('order:restaurant:update')
            ->setDescription('This command provide you an interface to update a restaurant entry.')
            ->addArgument(
                'commandName',
                InputArgument::REQUIRED
            )
            ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var RestaurantRepository $restaurantRepository */
        $restaurantRepository = $this->entityManager->getRepository('SlackOrder\Entity\Restaurant');
        /** @var Restaurant $restaurant */
        $restaurant = $restaurantRepository->findOneBy(['command' => $input->getArgument('commandName')]);

        if (null === $restaurant) {
            throw new \InvalidArgumentException('This command doesn\'t exists.');
        }
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Witch field do you want to set ?',
            [
                'Token', 'Start hour', 'End hour', 'Phone number', 'Email', 'Name', 'Example',
                'Sender email', 'Send order by email [0/1]'
            ],
            0
        );

        $question->setErrorMessage('Invalid field %s.');
        $field = $helper->ask($input, $output, $question);

        $newValueQuestion = new Question(sprintf('The new value of %s : ', $field));
        $newValue = $helper->ask($input, $output, $newValueQuestion);

        switch ($field) {
            case 'Token':
                $restaurant->setToken($newValue);
                break;
            case 'Start hour':
                $restaurant->setStartHour($newValue);
                break;
            case 'End hour':
                $restaurant->setEndHour($newValue);
                break;
            case 'Phone number':
                $restaurant->setPhoneNumber($newValue);
                break;
            case 'Email':
                $restaurant->setEmail($newValue);
                break;
            case 'Name':
                $restaurant->setName($newValue);
                break;
            case 'Url menu':
                $restaurant->setUrlMenu($newValue);
                break;
            case 'Example':
                $restaurant->setExample($newValue);
                break;
            case 'Sender email':
                $restaurant->setSenderEmail($newValue);
                break;
            case 'Send order by email [0/1]':
                $restaurant->setSendOrderByEmail($newValue);
                break;
        }

        $this->validateRestaurant($restaurant);
        $this->entityManager->persist($restaurant);
        $this->entityManager->flush();
    }
}
