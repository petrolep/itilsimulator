<?php
/**
 * ScenarioDesignResultTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.5.13 17:30
 */

namespace ITILSimulator\Tests;

use ITILSimulator\Entities\Session\TrainingStep;
use ITILSimulator\Entities\Training\ScenarioDesignResult;

require_once(__DIR__ . '/../../../bootstrap.php');

class ScenarioDesignResultTest extends ITIL_TestCase {
	private $fields = array(
		'comment' => 'comment',
		'metadata' => 'metadata',
		'purchaseCost' => 11.50,
		'operationCost' => 5.50,
	);

	public function testGetSet() {
		$result = $this->getScenarioDesignResult();

		$this->runGetSetTest($result, $this->fields);
		$this->assertNull($result->getTrainingStep());
	}

	public function testRelations() {
		$result = $this->getScenarioDesignResult();

		$trainingStep = new TrainingStep();
		$result->setTrainingStep($trainingStep);
		$this->assertSame($trainingStep, $result->getTrainingStep());
	}

	protected function getScenarioDesignResult() {
		$result = new ScenarioDesignResult();
		$this->initObject($result, $this->fields);

		return $result;
	}
}
