<?php
// Here you can initialize variables that will for your tests

if (!function_exists('test_login')) {
	function test_login(WebGuy $I) {
		$I->amOnPage('/');
		$I->fillField('username', 'admin@example.com');
		$I->fillField('password', 'admin@example.com');
		$I->click('send');

		// selenium wait for ajax
		//$I->waitForJS(10000, 'false');
		selenium_wait_load($I);
		//$I->waitForText('Global');
	}
}

if (!function_exists('selenium_wait_load')) {
	function selenium_wait_load(WebGuy $I) {
		$I->executeInSelenium(function(\Selenium\Browser $browser) {
		  $browser->waitForPageToLoad(100000);
		});
	}
}

require __DIR__ . '/../_bootstrap.php';