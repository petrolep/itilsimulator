<?php
/**
 * InputOutputTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.5.13 16:57
 */

namespace ITILSimulator\Tests;

use ITILSimulator\Entities\Training\InputOutput;
use ITILSimulator\Entities\Training\Training;

require_once(__DIR__ . '/../../../bootstrap.php');

class InputOutputTest extends ITIL_TestCase {
	private $fields = array(
		'name' => 'testIO',
		'code' => 'TEST_IO',
	);

	public function testGetSet() {
		$io = $this->getIO();

		$this->runGetSetTest($io, $this->fields);

		$this->assertNull($io->getTraining());
	}

	public function testRelations() {
		$io = $this->getIO();

		$training = new Training();
		$training->setName('Test training');

		$io->setTraining($training);
		$this->assertSame($training, $io->getTraining());
	}

	public function getIO() {
		$io = new InputOutput();
		$this->initObject($io, $this->fields);

		return $io;
	}
}
