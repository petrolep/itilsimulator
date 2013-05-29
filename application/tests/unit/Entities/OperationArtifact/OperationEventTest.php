<?php
/**
 * OperationEventTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 9:22
 */

namespace ITILSimulator\Tests;

use ITILSimulator\Entities\OperationArtifact\OperationEvent;
use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Entities\Session\TrainingStep;

require_once(__DIR__ . '/../../../bootstrap.php');


class OperationEventTest extends ITIL_TestCase {
	private $fields = array(
		'source' => 'Event source',
		'code' => 'Event code',
		'description' => 'Event description',
		'originalId' => 11,
		'status' => 2,
	);

	public function testGetSet() {
		$event = new OperationEvent();
		$this->assertEquals(0, $event->getOriginalId());
		$this->assertFalse($event->getIsUndid());

		$this->runGetSetTest($event, $this->fields);

		$this->assertNull($event->getScenarioStepFrom());
		$this->assertNull($event->getScenarioStepTo());
		$this->assertFalse($event->getIsUndid());

		// date
		$date = new \DateTime();
		$event->setDate($date);
		$this->assertEquals($date, $event->getDate());

		// archive event
		$event->setArchived(false);
		$this->assertFalse($event->getIsArchived());
		$this->assertFalse($event->isArchived());
		$event->archive();
		$this->assertTrue($event->getIsArchived());
		$this->assertTrue($event->isArchived());

		// validate + invalidate
		$scenarioStepFrom = new ScenarioStep(new TrainingStep());
		$event->validate($scenarioStepFrom);
		$this->assertSame($scenarioStepFrom, $event->getScenarioStepFrom());
		$this->assertNull($event->getScenarioStepTo());

		$scenarioStepTo = new ScenarioStep(new TrainingStep());
		$event->invalidate($scenarioStepTo);
		$this->assertSame($scenarioStepTo, $event->getScenarioStepTo());
		$this->assertSame($scenarioStepFrom, $event->getScenarioStepFrom());
	}
}
