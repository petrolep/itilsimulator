<?php
/**
 * TrainingTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.5.13 18:20
 */

namespace ITILSimulator\Tests;

use ITILSimulator\Entities\Training\KnownIssue;
use ITILSimulator\Entities\Training\Training;

require_once(__DIR__ . '/../../../bootstrap.php');

class TrainingTest extends ITIL_TestCase {
	private $fields = array(
		'name' => 'Training test',
		'shortDescription' => 'short description',
		'description' => 'description',
		'isPublished' => true,
	);

	public function testGetSet() {
		$training = $this->getTraining();

		$this->runGetSetTest($training, $this->fields);

		$this->assertNull($training->getUser());
		$this->assertEquals(0, $training->getScenarios()->count());
		$this->assertEquals(0, $training->getServices()->count());
		$this->assertEquals(0, $training->getInputsOutputs()->count());
		$this->assertEquals(0, $training->getKnownIssues()->count());
		$this->assertEquals(0, $training->getOperationCategories()->count());
	}

	public function testRelations() {
		$training = $this->getTraining();

		$issue = new KnownIssue();
		$issue->setCode('issue');
		$training->addKnownIssue($issue);
		$issue->setTraining($training);

		$this->assertSame($issue, $training->getKnownIssues()->first());
	}

	protected function getTraining() {
		$training = new Training();
		$this->initObject($training, $this->fields);

		return $training;
	}
}
