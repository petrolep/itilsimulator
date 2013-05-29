<?php
$path = __DIR__ . '/../../..';
require_once($path . '/libs/autoload.php');

$configurator = new Nette\Config\Configurator;
$configurator->setDebugMode(FALSE);
$configurator->setTempDirectory($path . '/temp');
$configurator->createRobotLoader()
	->addDirectory($path . '/app')
	->register();

$configurator->addConfig($path . '/app/config/config.neon');
$configurator->addConfig($path . '/app/config/config.local.neon', $configurator::NONE); // none section
$configurator->addConfig($path . '/app/config/config.tests.neon', $configurator::NONE);
$configurator->onCompile[] = function ($configurator, $compiler) {
    $compiler->addExtension('gettextTranslator', new GettextTranslator\DI\Extension);
};

$container = $configurator->createContainer();

/** @var \Doctrine\ORM\EntityManager $em */
$em = $container->getService('doctrineEntityManager');

/** @var \ITILSimulator\Base\DoctrineFactory $emFactory */
$emFactory = $container->getService('doctrineFactory');
$config = $emFactory->getConfiguration();

// refresh database -- drop all existing tables and create a new fresh instance
if (isset($config['dump']) && $dumpFile = $config['dump']) {
	$dump = file_get_contents($path . '/' . $dumpFile);
	$em->getConnection()->executeQuery($dump);
}