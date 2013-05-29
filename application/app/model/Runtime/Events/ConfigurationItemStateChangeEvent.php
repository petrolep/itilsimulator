<?php
/**
 * ConfigurationItemStateChangeRequestEvent.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 23.4.13 19:53
 */

namespace ITILSimulator\Runtime\Events;

/**
 * EventManager event about ConfigurationItem state/value change
 * @package ITILSimulator\Runtime\Events
 */
class ConfigurationItemStateChangeEvent extends Event
{
	#region "Properties"

	/** @var string */
	protected $serviceCode;

	/** @var string */
	protected $configurationItemCode;

	/** @var string */
	protected $key;

	/** @var string */
	protected $value;

	#endregion

	public function __construct($serviceCode, $configurationItemCode, $key, $value)
	{
		$this->serviceCode = $serviceCode;
		$this->configurationItemCode = $configurationItemCode;
		$this->key = $key;
		$this->value = $value;
	}

	#region "Get & set"

	/**
	 * @param string $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param string $serviceCode
	 */
	public function setServiceCode($serviceCode)
	{
		$this->serviceCode = $serviceCode;
	}

	/**
	 * @return string
	 */
	public function getServiceCode()
	{
		return $this->serviceCode;
	}

	/**
	 * @param string $key
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}

	/**
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @param string $configurationItemCode
	 */
	public function setConfigurationItemCode($configurationItemCode)
	{
		$this->configurationItemCode = $configurationItemCode;
	}

	/**
	 * @return string
	 */
	public function getConfigurationItemCode()
	{
		return $this->configurationItemCode;
	}

	#endregion
}