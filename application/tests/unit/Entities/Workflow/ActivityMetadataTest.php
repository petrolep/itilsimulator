<?php
/**
 * ActivityMetadataTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 11:11
 */

namespace ITILSimulator\Tests;


use ITILSimulator\Base\Position;
use ITILSimulator\Entities\Workflow\ActivityMetadata;

require_once(__DIR__ . '/../../../bootstrap.php');

class ActivityMetadataTest extends ITIL_TestCase {
	public function testGetSet() {
		$m = new ActivityMetadata();
		$this->assertNotNull($m->getPosition());

		$position = new Position(10, 20);
		$m->setPosition($position);

		$this->assertSame($position, $m->getPosition());
	}
}
