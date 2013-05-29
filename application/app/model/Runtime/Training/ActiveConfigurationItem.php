<?php
/**
 * ActiveCobnfigurationItem.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 11:11
 */

namespace ITILSimulator\Runtime\Training;


use ITILSimulator\Entities\OperationArtifact\OperationEvent;
use ITILSimulator\Runtime\Events\ConfigurationItemEvent;
use ITILSimulator\Runtime\Events\Event;
use ITILSimulator\Runtime\Events\EventTypeEnum;
use ITILSimulator\Runtime\RuntimeContext\ConfigurationItemRuntimeContext;
use ITILSimulator\Runtime\Training\ISpecifiableObject;
use ITILSimulator\Entities\Training\ConfigurationItem;
use ITILSimulator\Entities\Training\ConfigurationItemSpecification;
use Nette\Object;

/**
 * Active configuration item holding reference of configuration item and its current valid specification
 * @package ITILSimulator\Runtime\Training
 */
class ActiveConfigurationItem extends Object implements ISpecifiableObject
{
	/** @var ConfigurationItem */
	protected $configurationItem;

	/** @var ConfigurationItemSpecification */
	protected $configurationItemSpecification;

	/** @var ConfigurationItemSpecification */
	protected $originalConfigurationItemSpecification;

	/** @var ConfigurationItemRuntimeContext */
	protected $runtimeContext;

	public function __construct(ConfigurationItem $configurationItem, ConfigurationItemSpecification $configurationItemSpecification) {
		$this->configurationItem = $configurationItem;
		$this->configurationItemSpecification = clone $configurationItemSpecification;
		$this->originalConfigurationItemSpecification = $configurationItemSpecification;
	}

	#region "Get & set"

	/**
	 * @return \ITILSimulator\Entities\Training\ConfigurationItem
	 */
	public function getConfigurationItem()
	{
		return $this->configurationItem;
	}

	/**
	 * @return \ITILSimulator\Entities\Training\ConfigurationItemSpecification
	 */
	public function getSpecification()
	{
		return $this->configurationItemSpecification;
	}

	/**
	 * @param \ITILSimulator\Runtime\RuntimeContext\ConfigurationItemRuntimeContext $runtimeContext
	 */
	public function setRuntimeContext($runtimeContext)
	{
		$this->runtimeContext = $runtimeContext;
	}

	/**
	 * @return \ITILSimulator\Runtime\RuntimeContext\ConfigurationItemRuntimeContext
	 */
	public function getRuntimeContext()
	{
		return $this->runtimeContext;
	}

	#endregion

	public function getConfigurationItemId()
	{
		return $this->configurationItem->getId();
	}

	/**
	 * Check if the specification was changed
	 * @return bool
	 */
	public function isSpecificationChanged() {
		return !$this->configurationItemSpecification->equals($this->originalConfigurationItemSpecification);
	}

	/**
	 * Calculate health level of configuration item based on custom attributes
	 * @return int
	 */
	public function getHealthLevel() {
		$health = 0;
		$attributes = $this->configurationItemSpecification->getAttributes();
		if (!$attributes)
			return 100;

		// custom weights for health calculation
		$boundaries = array('0' => 100, '0.5' => 50, '2' => 10, '3' => -30, '10' => -100);

		foreach ($attributes as $attribute) {
			$found = false;
			foreach ($boundaries as $offset => $penalty) {
				if ($attribute->getCurrentValue() * (1 + $offset) >= $attribute->getMinimumValue()
					&& ($attribute->getCurrentValue() * (1 - $offset) <= $attribute->getMaximumValue() || !$attribute->getMaximumValue())) {
					$health += $penalty;
					$found = true;

					break;
				}
			}

			if (!$found) {
				// extreme deviation
				$health -= 1000;
			}
		}

		return max(0, $health / count($attributes));
	}

	/**
	 * Ping configuration item
	 */
	public function ping() {
		if ($this->runtimeContext)
			$this->configurationItemSpecification->onPing($this->runtimeContext);
	}

	/**
	 * Receive input
	 * @param string $ioId Input/Output code
	 */
	public function receiveInput($ioId) {
		$this->configurationItemSpecification->onInputReceived($this->runtimeContext, $ioId);
	}

	/**
	 * Restart configuration item
	 */
	public function restart() {
		if ($this->runtimeContext)
			$this->configurationItemSpecification->onRestart($this->runtimeContext);

		$event = new Event();
		$event->setSource($this->getConfigurationItem()->getCode());

		$this->runtimeContext->getEventManager()->dispatch(EventTypeEnum::RUNTIME_CONFIGURATION_ITEM_RESTARTED, $event);
	}

	/**
	 * Replace configuration item
	 */
	public function replace() {
		if ($this->runtimeContext)
			$this->configurationItemSpecification->onReplace($this->runtimeContext);

		$event = new ConfigurationItemEvent($this->configurationItem);
		$event->setSource($this->getConfigurationItem()->getCode());
		$event->setCode($this->getConfigurationItem()->getCode());

		// restore original specification
		$this->configurationItemSpecification = clone $this->configurationItem->getDefaultSpecification();

		$this->runtimeContext->getEventManager()->dispatch(EventTypeEnum::RUNTIME_CONFIGURATION_ITEM_REPLACED, $event);
	}

	/**
	 * Init configuration item
	 */
	public function init() {
		foreach($this->configurationItem->getInputs() as $input) {
			// attach to event manager
			$this->runtimeContext->getEventManager()->addIOListener($input->getCode(), $this);
		}
	}

	/**
	 * Get code of configuration item
	 * @return string
	 */
	public function getCICode() {
		return $this->configurationItem->getCode();
	}
}