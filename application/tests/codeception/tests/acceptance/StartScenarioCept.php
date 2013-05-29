<?php
$I = new WebGuy($scenario);
$I->wantTo('check I can start new training');

test_login($I);

// select training
$I->amGoingTo('select training to be started');
$I->amOnPage('dashboard/');
$I->click('a.btn-link[href*="info/detail"]');

selenium_wait_load($I);

// start training
$I->amGoingTo('start the training');
$I->seeLink('Start this training');
$I->click('.highlight-box a');

selenium_wait_load($I);

// start scenario
$I->expect('available scenarios to be shown');
$I->see('Scenarios');
$I->seeLink('start scenario');
$I->dontSee('continue scenario');

// start
$I->amGoingTo('start the first scenario');
$I->click('a.btn-link.cta');

selenium_wait_load($I);

// design
$I->expect('service designer to be open');
$I->see('Design service');

$I->moveBack();
selenium_wait_load($I);

$I->expect('the designer scenario to be started');
$I->seeLink('continue scenario');

// service operation
$I->amGoingTo('start the service operation scenario');
$I->seeLink('start scenario');
$I->click('a.btn-link:not(.cta)');

selenium_wait_load($I);

$I->see('Service catalog');