<?php
/**
 * ActiveConfigurationItemFacade.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 2.5.13 0:26
 */

namespace ITILSimulator\Runtime\RuntimeContext;


use ITILSimulator\Entities\Training\ConfigurationItemSpecification;
use ITILSimulator\Runtime\RuntimeContext\ConfigurationItemRuntimeContext;
use ITILSimulator\Runtime\Training\ActiveConfigurationItem;
use ITILSimulator\Runtime\Training\PriorityEnum;
use Nette\MemberAccessException;
use Nette\Object;

/**
 * Facade for public access from custom behavior code in configuration items.
 * @package ITILSimulator\Runtime\RuntimeContext
 */
class ActiveConfigurationItemFacade extends Object
{
	#region "Properties"

	/** @var ActiveConfigurationItem  */
	protected $activeConfigurationItem;

	/** @var ConfigurationItemRuntimeContext  */
	protected $runtimeContext;

	/** @var ConfigurationItemSpecification  */
	protected $specification;

	#endregion

	public function __construct(ActiveConfigurationItem $activeConfigurationItem, ConfigurationItemRuntimeContext $runtimeContext) {
		$this->activeConfigurationItem = $activeConfigurationItem;
		$this->runtimeContext = $runtimeContext;
		$this->specification = $activeConfigurationItem->getSpecification();
	}

	#region "Custom data"

	/**
	 * Return custom data
	 * @param string|null $key If key is provided, data with selected key is returned. Otherwise array of all keys are returned.
	 * @return array|null
	 */
	public function getData($key = NULL) {
		if ($key)
			return $this->specification->getData($key);

		return NULL;
	}

	/**
	 * Set custom data
	 * @param string $key Name of value
	 * @param string $value Value
	 */
	public function setData($key = NULL, $value = NULL) {
		if ($key)
			$this->specification->setDataValue($key, $value);
	}

	/**
	 * Return property value or value from data collection (can be used as a shortcut for "getData")
	 * @param $name
	 * @return array|mixed|null
	 */
	public function &__get($name)
	{
		try {
			return parent::__get($name);

		} catch(MemberAccessException $e) {
			$val = $this->specification->getData($name);
			return $val;
		}
	}

	/**
	 * Set property value or value from data collection (can be used as a shortcut for "setData")
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value)
	{
		try {
			parent::__set($name, $value);

		} catch(MemberAccessException $e) {
			$this->specification->setDataValue($name, $value);
		}
	}

	#endregion

	#region "Custom attributes"

	/**
	 * Value of custom attribute defined by training creator.
	 * @param string|null $key If key is empty, array of all attributes is returned.
	 * @return int|string
	 */
	public function getAttribute($key = NULL) {
		$attribute = $this->specification->getAttribute($key);
		if ($attribute)
			return $attribute->getCurrentValue();

		return 0;
	}

	/**
	 * Set value of attribute defined by training creator.
	 * @param string $key Attribute name (code)
	 * @param string $value Attribute value
	 */
	public function setAttribute($key = NULL, $value = NULL) {
		$attribute = $this->specification->getAttribute($key);
		if ($attribute) {
			$attribute->setCurrentValue($value);
		}
	}

	#endregion

	#region "Get & set"

	/**
	 * Does nothing. Exists only to prevent users for accessing the property.
	 * @param null $value
	 */
	public function setHealthLevel($value = NULL) { }

	/**
	 * Returns current configuration item health level (in percentage). Is calculated dynamically based on
	 * state of all attributes.
	 * @return int|mixed
	 */
	public function getHealthLevel() {
		return $this->activeConfigurationItem->getHealthLevel();
	}

	public function setOperationalCosts($value) {
		$this->specification->setOperationalCosts($value);
	}

	public function getOperationalCosts($value) {
		$this->specification->getOperationalCosts();
	}

	/**
	 * Set priority. Possible values: PriorityEnum::CRITICAL, PriorityEnum::HIGH, PriorityEnum::MEDIUM, PriorityEnum::LOW
	 * @param int $value
	 */
	public function setPriority($value) {
		if (in_array($value, array(PriorityEnum::CRITICAL, PriorityEnum::HIGH, PriorityEnum::MEDIUM, PriorityEnum::LOW)))
			$this->specification->setPriority($value);
	}

	/**
	 * Returns current priority
	 */
	public function getPriority() {
		$this->specification->getPriority();
	}

	#endregion

	#region "Operations"

	/**
	 * Configuration item can create event (ITIL event)
	 * @param string $name Name of the event
	 * @param string $description Description of the event
	 */
	public function createEvent($name, $description) {
		$this->runtimeContext->createEvent($name, $description);
	}

	/**
	 * Generate output. Output is distributed to other configuration items which expects the output as their input.
	 * @param string $ioId
	 */
	public function generateOutput($ioId = NULL) {
		// execute onReceived event
		if ($ioId)
			$this->runtimeContext->getEventManager()->dispatchIO($ioId);
	}

	#endregion
}