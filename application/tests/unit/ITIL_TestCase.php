<?php
/**
 * ITIL_TestCase.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 16.5.13 23:14
 */
namespace ITILSimulator\Tests;

use Nette\Object;

class ITIL_TestCase extends \PHPUnit_Framework_TestCase
{
	// disable serialization of Nette environment ("Serialization of 'Closure' is not allowed")
	protected $backupGlobalsBlacklist = array('configurator');

	protected function runGetSetTest(Object $object, array $fields) {
		$this->initObject($object, $fields);

		foreach ($fields as $field => $value) {
			$this->assertEquals($value, $object->$field, 'Field ' . $field . ' does not match expecting value ' . $value);
		}
	}

	protected function initObject(Object $object, array $fields) {
		foreach ($fields as $field => $value) {
			$object->$field = $value;
		}
	}
}