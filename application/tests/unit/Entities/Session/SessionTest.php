<?php
/**
 * SessionTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 10:00
 */

namespace ITILSimulator\Tests;


use ITILSimulator\Entities\Session\Session;
use ITILSimulator\Entities\Simulator\User;
use ITILSimulator\Entities\Training\Training;

require_once(__DIR__ . '/../../../bootstrap.php');


class SessionTest extends ITIL_TestCase {
	public function testGetSet() {
		$user = new User();
		$training = new Training();
		$session = new Session($user, $training);

		$this->assertSame($user, $session->getUser());
		$this->assertSame($training, $session->getTraining());
		$this->assertFalse($session->isFinished());

		$this->assertEquals($session->getDateStart(), $session->getDateEnd());

		$session->finish();
		$this->assertTrue($session->isFinished());
		$this->assertTrue($session->getIsFinished());

		$this->assertEquals(0, $session->getTrainingSteps()->count());

		$date = new \DateTime();
		$session->setDateEnd($date);
		$this->assertSame($date, $session->getDateEnd());
	}

	public function testRelations() {
		$userMock = $this->getMock('ITILSimulator\Entities\Simulator\User', array('getId'));
		$userMock->expects($this->atLeastOnce())
			->method('getId')
			->will($this->returnValue(11));

		$trainingMock = $this->getMock('ITILSimulator\Entities\Training\Training', array('getUserId'));
		$trainingMock->expects($this->atLeastOnce())
			->method('getUserId')
			->will($this->returnValue(22));

		$session = new Session($userMock, $trainingMock);
		$this->assertEquals(11, $session->getUserId());
		$this->assertEquals(22, $session->getTrainingCreatorUserId());
	}
}
