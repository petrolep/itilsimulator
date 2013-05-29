<?php

require __DIR__ . '/../libs/autoload.php';

if (!include __DIR__ . '/../libs/Nette/Tester/Tester/bootstrap.php') {
	die('Install Nette Tester using `composer update --dev`');
}

require __DIR__ . '/unit/ITIL_TestCase.php';

function id($val) {
	return $val;
}

$configurator = new Nette\Config\Configurator;
$configurator->setDebugMode(FALSE);
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(__DIR__ . '/../app')
	->register();

$configurator->addConfig(__DIR__ . '/../app/config/config.neon');
$configurator->addConfig(__DIR__ . '/../app/config/config.local.neon', $configurator::NONE); // none section
$configurator->onCompile[] = function ($configurator, $compiler) {
    $compiler->addExtension('gettextTranslator', new GettextTranslator\DI\Extension);
};

return $configurator->createContainer();
