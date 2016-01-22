#!/usr/bin/env php
<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once __DIR__.'/../vendor/autoload.php';

$dbConfig = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration([__DIR__. '/../src'], true, null, null, false);
$conn = array(
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/../data/db.sqlite',
);
$entityManager = \Doctrine\ORM\EntityManager::create($conn, $dbConfig);
$helper = ConsoleRunner::createHelperSet($entityManager);
ConsoleRunner::run($helper);
