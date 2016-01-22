<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new \SlackOrder\Application();
$app->init(true);
$app->run();
