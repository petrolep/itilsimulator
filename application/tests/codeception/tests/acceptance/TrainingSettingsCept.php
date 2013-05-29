<?php
$I = new WebGuy($scenario);
$I->wantTo('ensure that training elements can be created');
$I->amOnPage('/');
$I->see('Start my session');

// login
test_login($I);
$I->see('System Administrator');

$I->amOnPage('/creator/training/list');

// create a new training
$I->amGoingTo('display new training form');
$I->seeLink('create a new training');
$I->click('a.new-link');
selenium_wait_load($I);

$I->amGoingTo('submit training form');
$trainingName = 'Acceptance test training';
$trainingDescription = 'My short description';
$I->see('Create a new training');
$I->fillField('name', $trainingName);
$I->fillField('shortDescription', $trainingDescription);
$I->uncheckOption('isPublished');
$I->click('submit_');

selenium_wait_load($I);

// training created
$I->expect('the training was created');
$I->see('was saved');

$I->expect('training detail is open');
$I->see('Services');
$I->see('Scenarios');
$I->see('Categories');

// add new service
$I->click('a[href*="service/new"]');

selenium_wait_load($I);

$I->fillField('code', 'SERVICE_CODE');
$I->fillField('name', 'Test service name');
$I->click('#custom-attributes .new-link');
$I->fillField('attribute_code_0', 'ATTR1');
$I->fillField('attribute_name_0', 'Test attribute');
$I->fillField('attribute_value_0', '123');

$I->click('save');

selenium_wait_load($I);

// check service was created
$I->expect('service was created');

$I->see('SERVICE_CODE');
$I->see('Test service name');

$I->expect('service attributes are as defined');
$I->see('Test attribute');
$I->see('123');

// add new configuration item
$I->amGoingTo('add new configuration item');
$I->click('.highlight-box a');

$I->waitForJS(5000, '$(".dialog").length > 0');

$I->fillField('code', 'CI_CODE');
$I->fillField('name', 'Test CI name');
$I->fillField('purchaseCosts', '999');

$I->click('save');

// check configuration item was created
$I->waitForJS(5000, '$(".list").length');

$I->expect('configuration item was created');

$I->see('Test CI name');
$I->see('CI_CODE');
$I->see('999');

// delete the testing scenario
$I->wantTo('delete the testing scenario');
$I->click('.back-link');

selenium_wait_load($I);

// delete training
$I->amGoingTo('delete the training');
$I->click('a.edit-link[href*="training/edit"]');

selenium_wait_load($I);

$I->seeLink('Delete training');
$I->click('a[href*=deleteTraining]');

selenium_wait_load($I);

$I->expect('the training was deleted');
$I->dontSee($trainingDescription);
