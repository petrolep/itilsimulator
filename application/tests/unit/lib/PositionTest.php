<?php
/**
 * PositionTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 11:12
 */

namespace ITILSimulator\Tests;


use ITILSimulator\Base\Position;

require_once(__DIR__ . '/../../bootstrap.php');

class PositionTest extends ITIL_TestCase {
	public function testGetSet() {
		$position = new Position(10, 20);

		$this->assertEquals(10, $position->getX());
		$this->assertEquals(20, $position->getY());
	}
}
