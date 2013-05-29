<?php
/**
 * ConfigurationItemTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.5.13 16:13
 */

namespace ITILSimulator\Tests;

use ITILSimulator\Entities\Training\ConfigurationItem;
use ITILSimulator\Entities\Training\InputOutput;
use ITILSimulator\Entities\Training\Service;

require_once(__DIR__ . '/../../../bootstrap.php');

class ConfigurationItemTest extends ITIL_TestCase {

	private $fields = array(
		'name' => 'testCi',
		'code' => 'TEST_CI',
		'description' => 'My test description',
		'isGlobal' => true,
		'expectedReliability' => 0.95,
	);

	public function testGetSet() {
		$ci = $this->getConfigurationItem();

		$this->runGetSetTest($ci, $this->fields);

		$this->assertEquals(0, $ci->getInputs()->count());
		$this->assertEquals(0, $ci->getOutputs()->count());
		$this->assertEquals(0, $ci->getSpecifications()->count());
		$this->assertEquals(0, $ci->getServices()->count());
		$this->assertNull($ci->getDefaultSpecification());
	}

	public function testService() {
		$ci = $this->getConfigurationItem();

		// create service
		$service = new Service();
		$service->setCode('service1');
		$this->assertEquals(0, $service->getConfigurationItems()->count());

		// add CI to the service
		$ci->addService($service);

		$this->assertEquals(1, $service->getConfigurationItems()->count());
		$this->assertEquals(1, $ci->getServices()->count());

		$this->assertSame($service, $ci->getServices()->first());
		$this->assertSame($ci, $service->getConfigurationItems()->first());

		// clear services
		$ci->clearServices();
		$this->assertEquals(0, $service->getConfigurationItems()->count());
		$this->assertEquals(0, $ci->getServices()->count());

		// add CI again to the service
		$ci->addService($service);
		$this->assertEquals(1, $service->getConfigurationItems()->count());
		$this->assertEquals(1, $ci->getServices()->count());

		// remove CI from the service
		$service->removeConfigurationItem($ci);
		$this->assertEquals(0, $service->getConfigurationItems()->count());
		$this->assertEquals(0, $ci->getServices()->count());
	}

	public function testInputs() {
		$ci = $this->getConfigurationItem();

		// create IO
		$io = new InputOutput();
		$io->setCode('io1');

		$ci->addInput($io);

		$this->assertEquals(1, $ci->getInputs()->count());
		$this->assertSame($io, $ci->getInputs()->first());

		$ci->clearInputs();
		$this->assertEquals(0, $ci->getInputs()->count());

		// add CI again to the service
		$io2 = new InputOutput();
		$io2->setCode('io2');

		$ci->addInput($io2);

		$ci->addInput($io);

		$this->assertSame($io, $ci->getInputs()->last());
		$ci->removeInput($io);

		$this->assertSame($io2, $ci->getInputs()->last());
	}

	public function testIO() {
		$ci = $this->getConfigurationItem();

		// create IO
		$io = new InputOutput();
		$io->setCode('io1');

		$ci->addOutput($io);

		$this->assertEquals(1, $ci->getOutputs()->count());
		$this->assertSame($io, $ci->getOutputs()->first());

		$ci->clearOutputs();
		$this->assertEquals(0, $ci->getOutputs()->count());

		// add CI again to the service
		$io2 = new InputOutput();
		$io2->setCode('io2');

		$ci->addInput($io2);

		$ci->addOutput($io);

		$this->assertSame($io, $ci->getOutputs()->last());
		$this->assertSame($io2, $ci->getInputs()->last());
		$ci->removeOutput($io);

		$this->assertEquals(0, $ci->getOutputs()->count());
		$this->assertSame($io2, $ci->getInputs()->last());
	}

	protected function getConfigurationItem() {
		$ci = new ConfigurationItem();
		$this->initObject($ci, $this->fields);

		return $ci;
	}
}
