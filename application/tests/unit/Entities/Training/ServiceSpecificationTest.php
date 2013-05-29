<?php
/**
 * ServiceSpecificationTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.5.13 18:06
 */

namespace ITILSimulator\Tests;

use ITILSimulator\Entities\Training\Service;
use ITILSimulator\Entities\Training\ServiceSpecification;

require_once(__DIR__ . '/../../../bootstrap.php');

class ServiceSpecificationTest extends ITIL_TestCase {
	const SERVICE_CODE = 'SERVICE_1';

	private $fields = array(
		'priority' => 2,
		'earnings' => 12.50,
		'isDefault' => true
	);

	public function testGetSet() {
		$specification = $this->getServiceSpecification();

		$this->runGetSetTest($specification, $this->fields);

		$this->assertNotNull($specification->getService());
		$specification->setAttribute('test', 'abc');
		$specification->setAttribute('test2', 'cde');
		$this->assertEquals('abc', $specification->getAttribute('test'));
		$specification->setAttribute('test2', 'xxx');
		$this->assertEquals('xxx', $specification->getAttribute('test2'));
	}

	/**
	 * @return ServiceSpecification
	 */
	protected function getServiceSpecification() {
		$service = new Service();
		$service->setCode(self::SERVICE_CODE);

		$specification = new ServiceSpecification($service);
		$this->initObject($specification, $this->fields);

		return $specification;
	}
}
