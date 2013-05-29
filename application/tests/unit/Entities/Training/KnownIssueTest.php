<?php
/**
 * KnownIssueTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.5.13 17:05
 */

namespace ITILSimulator\Tests;


use ITILSimulator\Entities\OperationArtifact\OperationCategory;
use ITILSimulator\Entities\Training\KnownIssue;
use ITILSimulator\Entities\Training\Training;

require_once(__DIR__ . '/../../../bootstrap.php');

class KnownIssueTest extends ITIL_TestCase {
	private $fields = array(
		'name' => 'Test issue',
		'code' => 'ISS_1',
		'keywords' => 'one, two, three',
		'description' => 'test description',
		'workaround' => 'test workaround',
		'workaroundCost' => 11.50,
		'fix' => 'test fix',
		'fixCost' => 22.50
	);

	public function testGetSet() {
		$issue = $this->getKnownIssue();

		$this->runGetSetTest($issue, $this->fields);

		$this->assertNull($issue->getCategory());
		$this->assertNull($issue->getTraining());
	}

	public function testRelations() {
		$issue = $this->getKnownIssue();

		$category = new OperationCategory();
		$category->setName('cat 1');
		$issue->setCategory($category);
		$this->assertSame($category, $issue->getCategory());

		$training = new Training();
		$training->setName('test training');
		$issue->setTraining($training);
		$this->assertSame($training, $issue->getTraining());
	}

	public function getKnownIssue() {
		$issue = new KnownIssue();
		$this->initObject($issue, $this->fields);

		return $issue;
	}
}
