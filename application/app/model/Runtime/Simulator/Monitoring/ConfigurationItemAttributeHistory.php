<?php
/**
 * ConfigurationItemAttributeHistory.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 13:40
 */

namespace ITILSimulator\Runtime\Simulator\Monitoring;


use ITILSimulator\Runtime\Simulator\Data;
use ITILSimulator\Runtime\Training\CustomServiceAttribute;
use Nette\Object;

/**
 * Container for reading custom attributes history from database
 * @package ITILSimulator\Runtime\Simulator
 */
class ConfigurationItemAttributeHistory extends Object
{
	protected $internalTime;

	/** @var CustomServiceAttribute[] */
	protected $attributes;
	protected $configurationItemID;
	protected $configurationItemName;

	/**
	 * @param $data Data from database (array)
	 */
	public function __construct($data) {
		$this->internalTime = $data['internalTime'];
		$this->attributes = $data['attributes'];
		$this->configurationItemID = $data['id'];
		$this->configurationItemName = $data['name'];
	}

	/**
	 * @param mixed $attributes
	 */
	public function setAttributes($attributes)
	{
		$this->attributes = $attributes;
	}

	/**
	 * @return \ITILSimulator\Runtime\Training\CustomServiceAttribute[]
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * @param mixed $configurationItemID
	 */
	public function setConfigurationItemID($configurationItemID)
	{
		$this->configurationItemID = $configurationItemID;
	}

	/**
	 * @return mixed
	 */
	public function getConfigurationItemID()
	{
		return $this->configurationItemID;
	}

	/**
	 * @param mixed $configurationItemName
	 */
	public function setConfigurationItemName($configurationItemName)
	{
		$this->configurationItemName = $configurationItemName;
	}

	/**
	 * @return mixed
	 */
	public function getConfigurationItemName()
	{
		return $this->configurationItemName;
	}

	/**
	 * @param mixed $internalTime
	 */
	public function setInternalTime($internalTime)
	{
		$this->internalTime = $internalTime;
	}

	/**
	 * @return mixed
	 */
	public function getInternalTime()
	{
		return $this->internalTime;
	}


}