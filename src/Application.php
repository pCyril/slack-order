<?php

namespace SlackOrder;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\Configuration;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use SlackOrder\Controller\OrderController;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\Loader\YamlFileLoader;
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

        // Configure Translator
        $this->register(new TranslationServiceProvider(), array(
            'locale_fallbacks' => $config['translator']['locale_fallbacks'],
        ));
        $this['translator'] = $this->share($this->extend('translator', function ($translator, $this) {
            $translator->addLoader('yaml', new YamlFileLoader());
            $translator->addResource('yaml', __DIR__.'/locales/fr.yml', 'fr');

            return $translator;
        }));

        // Controllers
        $orderController = new OrderController();
        $this->get('/', function() {
            return 'Server Running';
        });
        $this->get('/{_locale}/order', [$orderController, 'orderAction']);
    }

}
