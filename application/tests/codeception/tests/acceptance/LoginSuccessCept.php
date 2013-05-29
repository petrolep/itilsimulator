<?php
//$scenario->group('acceptance');

$I = new WebGuy($scenario);
$I->wantTo('ensure that front page works');
$I->amOnPage('/');
$I->see('Start my session');

// login
test_login($I);
$I->see('System Administrator');

// logout
$I->click('a[href$="user/out"]');

// dashboard not available after logout
$I->amOnPage('/dashboard/');
$I->see('Start my session');