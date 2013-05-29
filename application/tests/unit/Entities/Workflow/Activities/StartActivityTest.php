<?php
/**
 * StartActivityTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 11:25
 */

namespace ITILSimulator\Tests\Activities;

use ITILSimulator\Entities\Workflow\Activities\StartActivity;
use ITILSimulator\Entities\Workflow\WorkflowActivitySpecification;
use ITILSimulator\Tests\ITIL_TestCase;

require_once(__DIR__ . '/../../../../bootstrap.php');

class StartActivityTest extends ITIL_TestCase {
	public function testGetSet() {
		$activity = new StartActivity();
		$specification = new WorkflowActivitySpecification($activity);

		$this->assertTrue($specification->isRunning());
	}
}
