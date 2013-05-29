<?php

$I = new WebGuy($scenario);
$I->wantTo('ensure that new trainings can be created');
$I->amOnPage('/');
$I->see('Start my session');

// login
test_login($I);
$I->see('System Administrator');

// enter creator zone
$I->amGoingTo('enter creator zone');
$I->click('a.top-link[href$="creator/"]');
selenium_wait_load($I);

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
$I->see($trainingName);
$I->see('not published');
$I->see($trainingDescription);

// edit the training
$I->amGoingTo('edit the training');
$I->seeLink('edit');
$I->click('a.edit-link[href*="training/edit"]');
selenium_wait_load($I);

$I->see('Edit training');
$I->seeInField('name', $trainingName);
$I->seeInField('description', $trainingDescription);

$trainingName = 'Updated name';
$trainingDescription = 'Updated description';
$I->fillField('name', $trainingName);
$I->fillField('description', $trainingDescription);
$I->click('submit_');

selenium_wait_load($I);

// training edited
$I->expect('changed training was saved');
$I->see($trainingName);
$I->see($trainingDescription);

// delete training
$I->amGoingTo('delete the training');
$I->click('a.edit-link[href*="training/edit"]');

selenium_wait_load($I);

$I->seeLink('Delete training');
$I->click('a[href*=deleteTraining]');

selenium_wait_load($I);

$I->expect('the training was deleted');
$I->dontSee($trainingDescription);

