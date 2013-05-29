<?php
/**
 * ServiceTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.5.13 17:34
 */

namespace ITILSimulator\Tests;


use ITILSimulator\Entities\Training\Service;
use ITILSimulator\Entities\Training\Training;

require_once(__DIR__ . '/../../../bootstrap.php');

class ServiceTest extends ITIL_TestCase {
	private $fields = array(
		'name' => 'Test issue',
		'code' => 'SERVICE_1',
		'description' => 'test description',
		'serviceOwner' => 'John Smith',
		'graphicDesignData' => 'data',
	);

	public function testGetSet() {
		$service = $this->getService();
		$this->runGetSetTest($service, $this->fields);

		$this->assertNull($service->getTraining());
		$this->assertNull($service->getDefaultSpecification());
		$this->assertEquals(0, $service->getConfigurationItems()->count());
		$this->assertEquals(0, $service->getServiceSpecifications()->count());
	}

	public function testTraining() {
		$service = $this->getService();

		$service2 = $this->getService();

		$training = new Training();
		$this->assertEquals(0, $training->getServices()->count());
		$this->assertNull($service->getTraining());

		$service->setTraining($training);
		$this->assertEquals(1, $training->getServices()->count());
		$this->assertSame($training, $service->getTraining());
		$this->assertSame($service, $training->getServices()->first());

		$service2->setTraining($training);
		$service->setTraining(null);
		$this->assertNull($service->getTraining());
		$this->assertEquals(1, $training->getServices()->count());
		$this->assertSame($service2, $training->getServices()->first());
	}

	protected function getService() {
		$service = new Service();
		$this->initObject($service, $this->fields);

		return $service;
	}
}
