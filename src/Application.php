<?php

namespace SlackOrder;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\Configuration;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use SlackOrder\Controller\OrderController;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;

class Application extends \Silex\Application {

    public function init($debug = false)
    {
        $this['debug'] = $debug;

        // Loading Configuration
        $config = Yaml::parse(file_get_contents(__DIR__. '/../config/config.yml'));
        $this['config'] = $config;

        // Configure Database
        $dbConfig = Setup::createAnnotationMetadataConfiguration([__DIR__], $debug, null, null, false);
        $conn = array(
            'driver' => 'pdo_sqlite',
            'path' => __DIR__ . '/../data/db.sqlite',
        );
        $this['doctrine.manager'] = EntityManager::create($conn, $dbConfig);

        // Configure Mailer
        $this['swiftmailer.options'] = $config['mailer'];
        $this->register(new SwiftmailerServiceProvider());

        // Configure Twig
        $this->register(new TwigServiceProvider(), [
            'twig.path' => __DIR__.'/views',
        ]);

        // Controllers
        $orderController = new OrderController();
        $this->get('/', function() {
            return 'Server Running';
        });
        $this->get('/order', [$orderController, 'orderAction']);
    }

}
