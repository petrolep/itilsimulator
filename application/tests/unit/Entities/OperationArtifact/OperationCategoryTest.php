<?php
/**
 * OperationCategoryTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 9:14
 */

namespace ITILSimulator\Tests;

use ITILSimulator\Entities\OperationArtifact\OperationCategory;
use ITILSimulator\Entities\Training\Training;

require_once(__DIR__ . '/../../../bootstrap.php');

class OperationCategoryTest extends ITIL_TestCase {
	private $fields = array(
		'name' => 'Category name',
	);


	public function testGetSet() {
		$category = new OperationCategory();

		$this->runGetSetTest($category, $this->fields);

		$this->assertNull($category->getTraining());
		$this->assertEquals(0, $category->getTrainingId());

		$training = new Training();
		$category->setTraining($training);
		$this->assertSame($training, $category->getTraining());
	}
}
