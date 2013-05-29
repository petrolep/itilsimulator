<?php
/**
 * OperationProblemTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 9:48
 */

namespace ITILSimulator\Tests;

use ITILSimulator\Entities\OperationArtifact\OperationCategory;
use ITILSimulator\Entities\OperationArtifact\OperationProblem;
use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Entities\Session\TrainingStep;

require_once(__DIR__ . '/../../../bootstrap.php');

class OperationProblemTest extends ITIL_TestCase {
	private $fields = array(
		'referenceNumber' => 'ABC-123',
		'history' => 'History',
		'priority' => 2,
		'symptoms' => 'Symptoms',
		'problemOwner' => 'John Smith',
	);

	public function testGetSet() {
		$problem = new OperationProblem();
		$this->assertEquals(0, $problem->getOriginalId());
		$this->assertFalse($problem->getIsUndid());

		$this->runGetSetTest($problem, $this->fields);

		// history
		$problem->setHistory('');
		$this->assertEquals('', $problem->getHistory());
		$problem->logHistory('log 1');
		$problem->logHistory('log 2');
		$this->assertContains('log 1', $problem->getHistory());
		$this->assertContains('log 2', $problem->getHistory());

		// validate + invalidate
		$scenarioStepFrom = new ScenarioStep(new TrainingStep());
		$problem->validate($scenarioStepFrom);
		$this->assertSame($scenarioStepFrom, $problem->getScenarioStepFrom());
		$this->assertNull($problem->getScenarioStepTo());

		$scenarioStepTo = new ScenarioStep(new TrainingStep());
		$problem->invalidate($scenarioStepTo);
		$this->assertSame($scenarioStepTo, $problem->getScenarioStepTo());
		$this->assertSame($scenarioStepFrom, $problem->getScenarioStepFrom());

		// category
		$this->assertNull($problem->getCategory());
		$category = new OperationCategory();
		$problem->setCategory($category);
		$this->assertSame($category, $problem->getCategory());
	}
}
