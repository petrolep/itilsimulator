<?php
/**
 * WorkflowActivityTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 11:26
 */

namespace ITILSimulator\Tests;

use ITILSimulator\Entities\Workflow\Activities\StartActivity;
use ITILSimulator\Entities\Workflow\ActivityMetadata;

require_once(__DIR__ . '/../../../bootstrap.php');

class WorkflowActivityTest extends ITIL_TestCase {
	protected $fields = array(
		'description' => 'Activity description',
		'onEvent' => 'onEvent text',
		'onEventRaw' => 'onEventRaw text',
		'onStart' => 'onStart text',
		'onStartRaw' => 'onStartRaw text',
		'onCancel' => 'onCancel text',
		'onCancelRaw' => 'onCancelRaw text',
		'onFinish' => 'onFinish text',
		'onFinishRaw' => 'onFinishRaw text',
		'onFlow' => 'onFlow text',
		'onFlowRaw' => 'onFlowRaw text',
	);

	public function testGetSet() {
		$activity = new StartActivity();

		$this->runGetSetTest($activity, $this->fields);

		$metadata = new ActivityMetadata();
		$activity->setMetadata($metadata);
		$this->assertEquals($metadata, $activity->getMetadata());
	}

	public function testRelations() {
		$activity = new StartActivity();

		$this->assertEquals(0, $activity->getNextActivities()->count());
		$this->assertEquals(0, $activity->getPreviousActivities()->count());

		$workflowMock = $this->getMock('\ITILSimulator\Entities\Workflow\Workflow', array('getId', 'getTrainingId', 'getCreatorUserId'));
		$workflowMock->expects($this->atLeastOnce())
			->method('getId')
			->will($this->returnValue(12));
		$workflowMock->expects($this->atLeastOnce())
			->method('getTrainingId')
			->will($this->returnValue(6));
		$workflowMock->expects($this->atLeastOnce())
			->method('getCreatorUserId')
			->will($this->returnValue(60));

		$activity->setWorkflow($workflowMock);
		$this->assertEquals(12, $activity->getWorkflowId());
		$this->assertEquals(6, $activity->getTrainingId());
		$this->assertEquals(60, $activity->getCreatorUserId());
	}

}
