<?php
/**
 * OperationIncidentTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 9:38
 */

namespace ITILSimulator\Tests;

use ITILSimulator\Entities\OperationArtifact\OperationCategory;
use ITILSimulator\Entities\OperationArtifact\OperationIncident;
use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Entities\Session\TrainingStep;

require_once(__DIR__ . '/../../../bootstrap.php');

class OperationIncidentTest extends ITIL_TestCase {
	private $fields = array(
		'referenceNumber' => 'ABC-123',
		'level' => 2,
		'canBeEscalated' => true,
		'history' => 'History',
		'priority' => 2,
		'urgency' => 1,
		'impact' => 3,
		'symptoms' => 'Symptoms',
		'timeToResponse' => 10,
		'timeToResolve' => 20,
		'isMajor' => true,
	);

	public function testGetSet() {
		$incident = new OperationIncident();
		$this->assertEquals(0, $incident->getOriginalId());
		$this->assertFalse($incident->getIsUndid());

		$this->runGetSetTest($incident, $this->fields);

		// time to response
		$incident->setTimeToResponse(20);
		$this->assertEquals(20, $incident->getTimeToResponse());
		$incident->assign();
		$this->assertEquals(0, $incident->getTimeToResponse());

		// history
		$incident->setHistory('');
		$this->assertEquals('', $incident->getHistory());
		$incident->logHistory('log 1');
		$incident->logHistory('log 2');
		$this->assertContains('log 1', $incident->getHistory());
		$this->assertContains('log 2', $incident->getHistory());

		// validate + invalidate
		$scenarioStepFrom = new ScenarioStep(new TrainingStep());
		$incident->validate($scenarioStepFrom);
		$this->assertSame($scenarioStepFrom, $incident->getScenarioStepFrom());
		$this->assertNull($incident->getScenarioStepTo());

		$scenarioStepTo = new ScenarioStep(new TrainingStep());
		$incident->invalidate($scenarioStepTo);
		$this->assertSame($scenarioStepTo, $incident->getScenarioStepTo());
		$this->assertSame($scenarioStepFrom, $incident->getScenarioStepFrom());

		// category
		$this->assertNull($incident->getCategory());
		$category = new OperationCategory();
		$incident->setCategory($category);
		$this->assertSame($category, $incident->getCategory());
	}
}
