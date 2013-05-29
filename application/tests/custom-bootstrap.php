<?php
define('APP_DIR', __DIR__ . '/../app');

use Nette\Application\Routers\Route;

require __DIR__ . '/../libs/autoload.php';

// Configure application
$configurator = new Nette\Config\Configurator;

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
 ->addDirectory(APP_DIR)
 ->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(APP_DIR . '/config/config.neon');
$container = $configurator->createContainer();

//Set up router
$container->removeService("router");
$container->addService("router", new Nette\Application\Routers\SimpleRouter());

//Start session
Nette\Environment::getSession()->start();