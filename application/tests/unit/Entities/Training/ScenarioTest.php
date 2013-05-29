<?php
/**
 * ScenarioTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.5.13 17:11
 */

namespace ITILSimulator\Tests;

use ITILSimulator\Entities\Training\Scenario;
use ITILSimulator\Entities\Training\Training;

require_once(__DIR__ . '/../../../bootstrap.php');

class ScenarioTest extends ITIL_TestCase {
	private $fields = array(
		'name' => 'Test issue',
		'description' => 'test description',
		'detailDescription' => 'detail description',
		'initialBudget' => 11.50,
	);

	public function testGetSet() {
		$scenario = $this->getScenario();
		parent::runGetSetTest($scenario, $this->fields);

		$this->assertNull($scenario->getTraining());
		$this->assertNull($scenario->getDesignService());
		$this->assertEquals(0, $scenario->getServices()->count());
		$this->assertEquals(0, $scenario->getWorkflows()->count());
	}

	public function testTraining() {
		$scenario = $this->getScenario();

		$training = new Training();
		$training->setName('test training');
		$this->assertEquals(0, $training->getScenarios()->count());

		$scenario->setTraining($training);
		$this->assertSame($training, $scenario->getTraining());
		$this->assertEquals(1, $training->getScenarios()->count());
		$this->assertSame($scenario, $training->getScenarios()->first());

		$scenario->setTraining(null);
		$this->assertNull($scenario->getTraining());
		$this->assertEquals(0, $training->getScenarios()->count());

		$scenario->setTraining($training);
		$training2 = new Training();
		$scenario->setTraining($training2);
		$this->assertEquals(0, $training->getScenarios()->count());
		$this->assertEquals(1, $training2->getScenarios()->count());
	}

	protected function getScenario() {
		$scenario = new Scenario();
		$this->initObject($scenario, $this->fields);

		return $scenario;
	}
}
