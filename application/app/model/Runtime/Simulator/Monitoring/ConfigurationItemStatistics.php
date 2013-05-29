<?php
/**
 * ConfigurationItemStatistics.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 13:53
 */

namespace ITILSimulator\Runtime\Simulator\Monitoring;


use Nette\Object;

/**
 * History of values of a configuration item -- collection of ConfigurationItemAttributeStatistics objects.
 * @package ITILSimulator\Runtime\Simulator\Monitoring
 */
class ConfigurationItemStatistics extends Object
{
	/** @var ConfigurationItemAttributeStatistics[] */
	protected $attributesHistory = array();

	/** @var string Configuration item name */
	protected $name;

	/** @var int Configuration item ID */
	protected $id;

	/**
	 * Add new custom attribute history
	 * @param ConfigurationItemAttributeStatistics $attributeHistory
	 */
	public function addAttributeHistory(ConfigurationItemAttributeStatistics $attributeHistory)
	{
		$this->attributesHistory[] = $attributeHistory;
	}

	/**
	 * @return ConfigurationItemAttributeStatistics[]
	 */
	public function getAttributesHistory()
	{
		return $this->attributesHistory;
	}

	/**
	 * @param mixed $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}
}