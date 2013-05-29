<?php
//$scenario->group('acceptance');

$I = new WebGuy($scenario);
$I->wantTo('check my training list');

test_login($I);

$I->amOnPage('/dashboard/');
$I->see('Your trainings');
$I->see('continue');
$I->see('Start new training');
$I->click('.trainings-list a.cta');
selenium_wait_load($I);

$I->see('Scenarios');
$I->seeLink('start scenario');
$I->seeLink('continue scenario');