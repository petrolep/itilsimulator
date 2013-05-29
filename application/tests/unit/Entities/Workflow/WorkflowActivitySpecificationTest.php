<?php
/**
 * WorkflowActivitySpecificationTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 11:15
 */

namespace ITILSimulator\Tests;

use ITILSimulator\Entities\Workflow\WorkflowActivitySpecification;
use ITILSimulator\Runtime\Workflow\WorkflowActivityStateEnum;

require_once(__DIR__ . '/../../../bootstrap.php');

class WorkflowActivitySpecificationTest extends ITIL_TestCase {
	protected $fields = array(
		'description' => 'specification description',
		'state' => WorkflowActivityStateEnum::RUNNING
	);

	/** @var WorkflowActivitySpecification */
	protected $fixture;

	public function setUp() {
		$this->fixture = $this->createSpecification();
	}

	public function testGetSet() {
		$specification = $this->fixture;

		$this->runGetSetTest($specification, $this->fields);

		// states
		$this->assertFalse($specification->isDefault());
		$this->assertTrue($specification->isRunning());
		$this->assertFalse($specification->isFinished());
		$this->assertFalse($specification->isWaiting());

		$specification->setState(WorkflowActivityStateEnum::WAITING);
		$this->assertFalse($specification->isRunning());
		$this->assertFalse($specification->isFinished());
		$this->assertTrue($specification->isWaiting());

		$specification->setState(WorkflowActivityStateEnum::FINISHED);
		$this->assertFalse($specification->isRunning());
		$this->assertTrue($specification->isFinished());
		$this->assertFalse($specification->isWaiting());
	}

	public function testEquals() {
		$specification = $this->fixture;
		$specification2 = $this->createSpecification();

		$specification->setState(WorkflowActivityStateEnum::RUNNING);
		$specification2->setState(WorkflowActivityStateEnum::RUNNING);
		$this->assertTrue($specification->equals($specification2));

		$specification2->setState(WorkflowActivityStateEnum::FINISHED);
		$this->assertFalse($specification->equals($specification2));
	}

	protected function createSpecification() {
		$activityMock = $this->getMock('ITILSimulator\Entities\Workflow\WorkflowActivity');

		return new WorkflowActivitySpecification($activityMock);
	}

}
