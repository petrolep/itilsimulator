<?php
// Here you can initialize variables that will for your tests

require __DIR__ . '/../_bootstrap.php';

\Codeception\Module\Doctrine2::$em = $em;