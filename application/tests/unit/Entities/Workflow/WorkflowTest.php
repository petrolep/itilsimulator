<?php
/**
 * WorkflowTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 11:03
 */

namespace ITILSimulator\Tests;

use ITILSimulator\Entities\Workflow\Workflow;

require_once(__DIR__ . '/../../../bootstrap.php');

class WorkflowTest extends ITIL_TestCase {
	public function testGetSet() {
		$workflow = new Workflow();
		$this->assertNull($workflow->getScenario());
		$this->assertNull($workflow->getStartActivity());

		$this->assertEquals(0, $workflow->getWorkflowActivities()->count());

		$scenarioMock = $this->getMock('\ITILSimulator\Entities\Training\Scenario', array('getTrainingId', 'getCreatorUserId'));
		$scenarioMock->expects($this->atLeastOnce())
			->method('getTrainingId')
			->will($this->returnValue(12));
		$scenarioMock->expects($this->atLeastOnce())
			->method('getCreatorUserId')
			->will($this->returnValue(7));

		$workflow->setScenario($scenarioMock);

		$this->assertEquals(7, $workflow->getCreatorUserId());
		$this->assertEquals(12, $workflow->getTrainingId());
	}
}
