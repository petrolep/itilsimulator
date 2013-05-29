<?php
/**
 * ConfigurationItemSpecification.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 16.5.13 23:08
 */

namespace ITILSimulator\Tests;

use ITILSimulator\Entities\Training\ConfigurationItem;
use ITILSimulator\Entities\Training\ConfigurationItemSpecification;
use ITILSimulator\Runtime\Training\CustomServiceAttribute;

require_once(__DIR__ . '/../../../bootstrap.php');

class ConfigurationItemSpecificationTest extends ITIL_TestCase
{
	private $fields = array(
		'priority' => 3,
		'purchaseCosts' => 10.5,
		'operationalCosts' => 5.5,
		'onInputReceived' => 'onInputReceived',
		'onInputReceivedRaw' => 'onInputReceivedRaw',
		'onPing' => 'onPing',
		'onPingRaw' => 'onPingRaw',
		'onRestart' => 'onRestart',
		'onRestartRaw' => 'onRestartRaw',
		'onReplace' => 'onReplace',
		'onReplaceRaw' => 'onReplaceRaw',
	);

	public function testGetSet() {
		$s = $this->getSpecification();

		$this->runGetSetTest($s, $this->fields);

		$s->setIsDefault(true);
		$this->assertTrue($s->getIsDefault());
		$this->assertTrue($s->isDefault());

		$s->setDataValue('mykey', 'myvalue');
		$this->assertEquals('myvalue', $s->getData('mykey'));
		$this->assertCount(1, $s->getData());

		$attr = new CustomServiceAttribute('myattribute', 'myvalue', 0, 0, 0);
		$s->setAttribute('myattribute', $attr);
		$this->assertEquals($attr, $s->getAttribute('myattribute'));
		$this->assertCount(1, $s->getAttributes());
	}

	public function testClone() {
		$s = $this->getSpecification();

		$attr = new CustomServiceAttribute('myattribute', 'myvalue', 0, 0, 0);
		$s->setAttribute('myattribute', $attr);

		$clone = clone $s;
		$this->assertEquals($s, $clone);
		$this->assertTrue($s->equals($clone));
		$this->assertTrue($clone->equals($s));

		$originalValue = $s->getAttribute('myattribute')->getCurrentValue();
		$s->setAttributeValue('myattribute', 'changedvalue');
		$this->assertFalse($s->equals($clone));
		$this->assertFalse($clone->equals($s));

		$s->setAttributeValue('myattribute', $originalValue);
		$this->assertTrue($s->equals($clone));
		$this->assertTrue($clone->equals($s));

		$s->setAttributeValue('nonExistingAttribute', 'xxx');
		$this->assertTrue($s->equals($clone));
		$this->assertTrue($clone->equals($s));
	}

	public function testRelations() {
		$specification = $this->getSpecification();

		$ci = new ConfigurationItem();
		$ci->setCode('aaaa');
		$specification->setConfigurationItem($ci);

		$this->assertSame($ci->getCode(), $specification->getConfigurationItemCode());
	}

	/**
	 * @return ConfigurationItemSpecification
	 */
	public function getSpecification() {
		$specification = new ConfigurationItemSpecification();
		$this->initObject($specification, $this->fields);

		return $specification;
	}
}