<?php
/**
 * CustomServiceAttribute.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 28.4.13 17:20
 */

namespace ITILSimulator\Runtime\Training;


use Nette\Object;

/**
 * Custom attribute for services and configuration items
 * @package ITILSimulator\Runtime\Training
 */
class CustomServiceAttribute extends Object
{
	#region "Properties"

	/** @var string */
	protected $name;

	/** @var string */
	protected $code;

	/** @var string */
	protected $currentValue;

	/** @var string */
	protected $defaultValue;

	/** @var string */
	protected $minimumValue;

	/** @var string */
	protected $maximumValue;

	/** @var string */
	protected $unit;

	#endregion

	/**
	 * @param string $name Name of custom attribute
	 * @param string $code Code of custom attribute
	 * @param float $currentValue Current value
	 * @param float $minimumValue Minimum value (required by SLA)
	 * @param float $maximumValue Maximum value (required by SLA)
	 * @param string|null $unit Unit
	 */
	public function __construct($name, $code, $currentValue, $minimumValue, $maximumValue, $unit = NULL)
	{
		$this->name = $name;
		$this->code = $code;
		$this->currentValue = $currentValue;
		$this->defaultValue = $currentValue;
		$this->minimumValue = $minimumValue;
		$this->maximumValue = $maximumValue;
		$this->unit = $unit;
	}

	public function hashCode() {
		return implode('|', array($this->name, $this->code, $this->currentValue, $this->defaultValue, $this->minimumValue, $this->maximumValue, $this->unit));
	}

	#region "Get & set"

	/**
	 * @param string $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $value
	 */
	public function setCurrentValue($value)
	{
		$this->currentValue = $value;
	}

	/**
	 * @return string
	 */
	public function getCurrentValue()
	{
		return $this->currentValue;
	}

	/**
	 * @param string $defaultValue
	 */
	public function setDefaultValue($defaultValue)
	{
		$this->defaultValue = $defaultValue;
	}

	/**
	 * @return string
	 */
	public function getDefaultValue()
	{
		return $this->defaultValue;
	}

	/**
	 * @param float $maximumValue
	 */
	public function setMaximumValue($maximumValue)
	{
		$this->maximumValue = $maximumValue;
	}

	/**
	 * @return float
	 */
	public function getMaximumValue()
	{
		return $this->maximumValue;
	}

	/**
	 * @param float $minimumValue
	 */
	public function setMinimumValue($minimumValue)
	{
		$this->minimumValue = $minimumValue;
	}

	/**
	 * @return float
	 */
	public function getMinimumValue()
	{
		return $this->minimumValue;
	}

	/**
	 * @param string $unit
	 */
	public function setUnit($unit)
	{
		$this->unit = $unit;
	}

	/**
	 * @return string
	 */
	public function getUnit()
	{
		return $this->unit;
	}

	#endregion
}