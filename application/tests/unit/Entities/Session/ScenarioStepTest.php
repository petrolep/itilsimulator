<?php
/**
 * ScenarioStepTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 9:51
 */

namespace ITILSimulator\Tests;

use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Entities\Session\TrainingStep;

require_once(__DIR__ . '/../../../bootstrap.php');


class ScenarioStepTest extends ITIL_TestCase {
	private $fields = array(
		'evaluationPoints' => 30,
		'budget' => 123.50,
		'internalTime' => 2,
		'servicesSettlementTime' => 1,
	);

	public function testGetSet() {
		$step = new ScenarioStep(new TrainingStep());

		$this->assertNotNull($step->getDate());
		$this->assertNull($step->getUndoDate());
		$this->assertEquals(0, $step->getConfigurationItemsSpecifications()->count());
		$this->assertEquals(0, $step->getServicesSpecifications()->count());
		$this->assertEquals(0, $step->getWorkflowActivitiesSpecifications()->count());

		$this->runGetSetTest($step, $this->fields);

		// undo step
		$step->undo();
		$this->assertTrue($step->isUndid());
		$this->assertNotNull($step->getUndoDate());

		// last activity
		$date = new \DateTime();
		$step->setLastActivityDate($date);
		$this->assertSame($date, $step->getLastActivityDate());
	}
}
