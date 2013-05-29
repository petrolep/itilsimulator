<?php
/**
 * FlowActivityTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 11:39
 */

namespace ITILSimulator\Tests\Activities;

use ITILSimulator\Entities\Workflow\Activities\FinishActivity;
use ITILSimulator\Entities\Workflow\Activities\FlowActivity;
use ITILSimulator\Entities\Workflow\Activities\StartActivity;
use ITILSimulator\Tests\ITIL_TestCase;

require_once(__DIR__ . '/../../../../bootstrap.php');

class FlowActivityTest extends ITIL_TestCase {
	public function testWorkflow() {
		$flowActivity = new FlowActivity();

		$startActivityMock = $this->getMock('ITILSimulator\Entities\Workflow\Activities\StartActivity', array('getId'));
		$startActivityMock->expects($this->atLeastOnce())
			->method('getId')
			->will($this->returnValue(8));

		$finishActivityMock = $this->getMock('ITILSimulator\Entities\Workflow\Activities\FinishActivity', array('getId'));
		$finishActivityMock->expects($this->atLeastOnce())
			->method('getId')
			->will($this->returnValue(11));

		$flowActivity->setSource($startActivityMock);
		$flowActivity->setTarget($finishActivityMock);

		$this->assertSame($startActivityMock, $flowActivity->getSource());
		$this->assertEquals(8, $flowActivity->getSourceId());

		$this->assertSame($finishActivityMock, $flowActivity->getTarget());
		$this->assertEquals(11, $flowActivity->getTargetId());

		$this->assertEquals(1, $startActivityMock->getNextActivities()->count());
		$this->assertEquals(1, $finishActivityMock->getPreviousActivities()->count());

		$this->assertSame($flowActivity, $startActivityMock->getNextActivities()->first());
		$this->assertSame($flowActivity, $finishActivityMock->getPreviousActivities()->first());
	}
}
