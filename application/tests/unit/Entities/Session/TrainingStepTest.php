<?php
/**
 * TrainingStepTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 10:37
 */

namespace ITILSimulator\Tests;

use ITILSimulator\Entities\Session\TrainingStep;

require_once(__DIR__ . '/../../../bootstrap.php');

class TrainingStepTest extends ITIL_TestCase {
	public function testGetSet() {
		$trainingStep = new TrainingStep();

		$this->assertNull($trainingStep->getScenario());
		$this->assertNull($trainingStep->getLastValidScenarioStep());
		$this->assertNull($trainingStep->getSession());

		$this->assertNotNull($trainingStep->getDateStart());
		$this->assertEquals($trainingStep->getDateStart(), $trainingStep->getDateEnd());

		$this->assertFalse($trainingStep->isFinished());
		$this->assertFalse($trainingStep->getIsFinished());

		$trainingStep->finish();

		$this->assertTrue($trainingStep->isFinished());
		$this->assertTrue($trainingStep->getIsFinished());
		$this->assertNotSame($trainingStep->getDateStart(), $trainingStep->getDateEnd());


		$trainingStep->setBudget(12.50);
		$this->assertEquals(12.50, $trainingStep->getBudget());
	}

	public function testRelations() {
		$scenarioMock = $this->getMock('\ITILSimulator\Entities\Training\Scenario', array('getId', 'isDesign'));
		$scenarioMock->expects($this->atLeastOnce())
			->method('getId')
			->will($this->returnValue(12));
		$scenarioMock->expects($this->atLeastOnce())
			->method('isDesign')
			->will($this->returnValue(true));

		$trainingStep = new TrainingStep();
		$trainingStep->setScenario($scenarioMock);

		$this->assertEquals(12, $trainingStep->getScenarioId());
		$this->assertTrue($trainingStep->isDesign());
	}
}
