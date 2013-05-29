<?php
/**
 * ConfigurationItemSpecification.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 13.4.13 15:41
 */

namespace ITILSimulator\Entities\Training;


use Doctrine\Common\Collections\ArrayCollection;
use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Runtime\RuntimeContext\ConfigurationItemRuntimeContext;
use ITILSimulator\Runtime\Training\CustomServiceAttribute;
use Nette\Object;

/**
 * Configuration item specification (Doctrine entity).
 * @Entity(repositoryClass="ITILSimulator\Repositories\Training\ConfigurationItemSpecificationRepository")
 * @Table(name="configuration_item_specifications")
 * @HasLifecycleCallbacks
 */
class ConfigurationItemSpecification extends Object
{
	#region "Properties"

	/**
	 * @Id
	 * @Column(type="integer") @GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Training\ConfigurationItem")
	 * @var ConfigurationItem
	 */
	protected $configurationItem;

	/**
	 * @Column(type="integer")
	 * @var int
	 */
	protected $priority;

	/**
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $isDefault = false;

	/**
	 * @Column(type="decimal")
	 * @var float
	 */
	protected $purchaseCosts = 0.0;

	/**
	 * @Column(type="decimal")
	 * @var float
	 */
	protected $operationalCosts = 0.0;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $onPing;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $onPingRaw;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $onInputReceived;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $onInputReceivedRaw;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $onRestart;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $onRestartRaw;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $onReplace;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $onReplaceRaw;

	/**
	 * @Column(type="array", nullable=true)
	 * @var array
	 */
	protected $data = array();

	/**
	 * @Column(type="array", nullable=true)
	 * @var CustomServiceAttribute[]
	 */
	protected $attributes = array();

	/**
	 * @ManyToMany(targetEntity="ITILSimulator\Entities\Session\ScenarioStep", mappedBy="configurationItemsSpecifications")
	 * @var ArrayCollection|ScenarioStep[]
	 */
	protected $scenarioSteps;

	#endregion

	public function __clone()
	{
		if (is_array($this->attributes) && $this->attributes) {
			foreach ($this->attributes as $key => $attribute) {
				$this->attributes[$key] = clone $attribute;
			}
		}
	}

	/**
	 * @param ConfigurationItemSpecification $other
	 * @return bool
	 */
	public function equals(ConfigurationItemSpecification $other) {
		$fields = array('getPriority', 'getPurchaseCosts', 'getOperationalCosts', 'getData');
		foreach ($fields as $field) {
			if ($this->$field() != $other->$field())
				return false;
		}

		$originalAttributes = $other->getAttributes();
		$newAttributes = $this->getAttributes();
		if (count($originalAttributes) != count($newAttributes)) {
			return false;
		}

		$originals = array();
		foreach ($originalAttributes as $originalAttribute) {
			$originals[] = $originalAttribute->hashCode();
		}

		foreach ($newAttributes as $newAttribute) {
			$hashCode = $newAttribute->hashCode();
			$key = array_search($hashCode, $originals);
			if ($key === FALSE) {
				// modified value
				return false;
			}

			unset($originals[$key]);
		}

		return true;
	}

	#region "Get & set"

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param \ITILSimulator\Entities\Training\ConfigurationItem $configurationItem
	 */
	public function setConfigurationItem($configurationItem)
	{
		$this->configurationItem = $configurationItem;
	}

	/**
	 * @return \ITILSimulator\Entities\Training\ConfigurationItem
	 */
	public function getConfigurationItem()
	{
		return $this->configurationItem;
	}

	/**
	 * @param int $priority
	 */
	public function setPriority($priority)
	{
		$this->priority = $priority;
	}

	/**
	 * @return int
	 */
	public function getPriority()
	{
		return $this->priority;
	}

	/**
	 * @param string $onRun
	 */
	public function setOnPing($onRun)
	{
		$this->onPing = $onRun;
	}

	/**
	 * @return string
	 */
	public function getOnPing()
	{
		return $this->onPing;
	}

	/**
	 * @param string $onPingRaw
	 */
	public function setOnPingRaw($onPingRaw)
	{
		$this->onPingRaw = $onPingRaw;
	}

	/**
	 * @return string
	 */
	public function getOnPingRaw()
	{
		return $this->onPingRaw;
	}

	/**
	 * @param string $onInputReceived
	 */
	public function setOnInputReceived($onInputReceived)
	{
		$this->onInputReceived = $onInputReceived;
	}

	/**
	 * @return string
	 */
	public function getOnInputReceived()
	{
		return $this->onInputReceived;
	}

	/**
	 * @param string $onInputReceivedRaw
	 */
	public function setOnInputReceivedRaw($onInputReceivedRaw)
	{
		$this->onInputReceivedRaw = $onInputReceivedRaw;
	}

	/**
	 * @return string
	 */
	public function getOnInputReceivedRaw()
	{
		return $this->onInputReceivedRaw;
	}

	/**
	 * @param string $onReplace
	 */
	public function setOnReplace($onReplace)
	{
		$this->onReplace = $onReplace;
	}

	/**
	 * @return string
	 */
	public function getOnReplace()
	{
		return $this->onReplace;
	}

	/**
	 * @param string $onReplaceRaw
	 */
	public function setOnReplaceRaw($onReplaceRaw)
	{
		$this->onReplaceRaw = $onReplaceRaw;
	}

	/**
	 * @return string
	 */
	public function getOnReplaceRaw()
	{
		return $this->onReplaceRaw;
	}

	/**
	 * @param string $onRestart
	 */
	public function setOnRestart($onRestart)
	{
		$this->onRestart = $onRestart;
	}

	/**
	 * @return string
	 */
	public function getOnRestart()
	{
		return $this->onRestart;
	}

	/**
	 * @param string $onRestartRaw
	 */
	public function setOnRestartRaw($onRestartRaw)
	{
		$this->onRestartRaw = $onRestartRaw;
	}

	/**
	 * @return string
	 */
	public function getOnRestartRaw()
	{
		return $this->onRestartRaw;
	}

	/**
	 * @param boolean $isDefault
	 */
	public function setIsDefault($isDefault)
	{
		$this->isDefault = $isDefault;
	}

	/**
	 * @return boolean
	 */
	public function getIsDefault()
	{
		return $this->isDefault;
	}

	/**
	 * @return bool
	 */
	public function isDefault()
	{
		return $this->getIsDefault();
	}

	/**
	 * @param float $operationalCosts
	 */
	public function setOperationalCosts($operationalCosts)
	{
		$this->operationalCosts = $operationalCosts;
	}

	/**
	 * @return float
	 */
	public function getOperationalCosts()
	{
		return $this->operationalCosts;
	}

	/**
	 * @param float $purchaseCosts
	 */
	public function setPurchaseCosts($purchaseCosts)
	{
		$this->purchaseCosts = $purchaseCosts;
	}

	/**
	 * @return float
	 */
	public function getPurchaseCosts()
	{
		return $this->purchaseCosts;
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function setDataValue($key, $value)
	{
		if (!is_array($this->data))
			$this->data = array();

		$this->data[$key] = $value;
	}

	/**
	 * @param string|null $key
	 * @return array|null
	 */
	public function getData($key = NULL)
	{
		if (!is_array($this->data))
			$this->data = array();

		if (!$key)
			return $this->data;

		if (!array_key_exists($key, $this->data))
			return NULL;

		return $this->data[$key];
	}

	/**
	 * @return CustomServiceAttribute[]
	 */
	public function getAttributes()
	{
		if (!is_array($this->attributes))
			return array();

		return $this->attributes;
	}

	/**
	 * @param string $key
	 * @return CustomServiceAttribute|null
	 */
	public function getAttribute($key) {
		if (!array_key_exists($key, $this->attributes))
			return NULL;

		return $this->attributes[$key];
	}

	public function clearAttributes()
	{
		$this->attributes = array();
	}

	/**
	 * @param string $name
	 * @param CustomServiceAttribute $value
	 */
	public function setAttribute($name, CustomServiceAttribute $value)
	{
		if (!is_array($this->attributes))
			$this->attributes = array();

		$this->attributes[$name] = $value;
	}

	/**
	 * @param string $name
	 * @param string $value
	 */
	public function setAttributeValue($name, $value)
	{
		if (!isset($this->attributes[$name]))
			return;

		$this->attributes[$name]->setCurrentValue($value);
	}

	#endregion

	#region "Events"

	/**
	 * Executed when "ping" event is received.
	 * @param ConfigurationItemRuntimeContext $context
	 * @return mixed
	 */
	public function onPing(ConfigurationItemRuntimeContext $context) {
		if ($this->onPing) {
			return $context->execute($this->onPing, null);
		}
	}

	/**
	 * Executed when input is received.
	 * @param ConfigurationItemRuntimeContext $context
	 * @param string $ioId
	 * @return mixed
	 */
	public function onInputReceived(ConfigurationItemRuntimeContext $context, $ioId) {
		if ($this->onInputReceived) {
			return $context->executeInput($this->onInputReceived, $ioId);
		}
	}

	/**
	 * Executed when configuration item is restarted
	 * @param ConfigurationItemRuntimeContext $context
	 * @return mixed
	 */
	public function onRestart(ConfigurationItemRuntimeContext $context) {
		if ($this->onRestart) {
			return $context->execute($this->onRestart, null);
		}
	}

	/**
	 * Executed when configuration item is replaced
	 * @param ConfigurationItemRuntimeContext $context
	 * @return mixed
	 */
	public function onReplace(ConfigurationItemRuntimeContext $context) {
		if ($this->onReplace) {
			return $context->execute($this->onReplace, null);
		}
	}

	#endregion

	#region "Connecting Get & set"

	/**
	 * @return int
	 */
	public function getConfigurationItemId() {
		return $this->configurationItem->getId();
	}

	/**
	 * @return string
	 */
	public function getConfigurationItemCode() {
		return $this->configurationItem->getCode();
	}

	#endregion
}