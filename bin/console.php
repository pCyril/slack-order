#!/usr/bin/env php
<?php

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Application;
use SlackOrder\Command\AddRestaurantCommand;
use SlackOrder\Command\UpdateRestaurantCommand;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
$application = new Application('SlackOrder');

// Configure Database
$dbConfig = Setup::createAnnotationMetadataConfiguration([__DIR__], true, null, null, false);
$conn = array(
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/../data/db.sqlite',
);
$entityManager = EntityManager::create($conn, $dbConfig);


$application->add(new AddRestaurantCommand($entityManager));
$application->add(new UpdateRestaurantCommand($entityManager));

$application->run();
