<?php
/**
 * ActiveService.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 13.4.13 19:17
 */

namespace ITILSimulator\Runtime\Training;

use ITILSimulator\Runtime\Training\ISpecifiableObject;
use ITILSimulator\Entities\Training\Service;
use ITILSimulator\Entities\Training\ServiceSpecification;
use ITILSimulator\Runtime\Training\ActiveConfigurationItem;
use Nette\Object;

/**
 * Active service holding reference of service and its current valid specification
 * @package ITILSimulator\Runtime\Training
 */
class ActiveService extends Object implements ISpecifiableObject
{
	/** @var Service */
	private $service;

	/** @var ServiceSpecification */
	private $serviceSpecification;

	/** @var ServiceSpecification */
	private $originalServiceSpecification;

	/** @var ActiveConfigurationItem[] */
	protected $configurationItems = array();

	public function __construct(Service $service, ServiceSpecification $serviceSpecification) {
		$this->service = $service;
		$this->serviceSpecification = clone $serviceSpecification;
		$this->originalServiceSpecification = $serviceSpecification;
	}

	/**
	 * Add new configuration item
	 * @param ActiveConfigurationItem $activeConfigurationItem
	 */
	public function addConfigurationItem(ActiveConfigurationItem $activeConfigurationItem) {
		$this->configurationItems[] = $activeConfigurationItem;
	}

	/**
	 * Return configuration items
	 * @return array|ActiveConfigurationItem[]
	 */
	public function getConfigurationItems()
	{
		return $this->configurationItems;
	}

	/**
	 * @return \ITILSimulator\Entities\Training\Service
	 */
	public function getService()
	{
		return $this->service;
	}

	/**
	 * @return \ITILSimulator\Entities\Training\ServiceSpecification
	 */
	public function getSpecification()
	{
		return $this->serviceSpecification;
	}

	/**
	 * Check if the specification was changed
	 * @return bool
	 */
	public function isSpecificationChanged() {
		$fields = array('priority', 'earnings');
		foreach ($fields as $field) {
			if ($this->serviceSpecification->$field != $this->originalServiceSpecification->$field)
				return true;
		}

		return false;
	}

	/**
	 * Check if a specification of any configuration item was changed
	 * @return bool
	 */
	public function isConfigurationItemsSpecificationChanged(){
		foreach($this->configurationItems as $ci) {
			if ($ci->isSpecificationChanged())
				return true;
		}

		return false;
	}

	/**
	 * Calculate health level of configuration item based on status of its configuration items
	 * @return float|int
	 */
	public function getHealthLevel() {
		// calculate health status based on configuration items
		$weights = array(PriorityEnum::LOW => 1, PriorityEnum::MEDIUM => 3, PriorityEnum::HIGH => 8, PriorityEnum::CRITICAL => 50);
		$health = 0;
		$expectedHealth = 0;
		foreach ($this->configurationItems as $ci) {
			$priority = $ci->getSpecification()->getPriority();
			$coef = isset($weights[$priority]) ? $weights[$priority] : 1;
			$level = $ci->getHealthLevel();
			if ($level < 90)
				$coef *= 2;
			$health += $level * $coef;
			$expectedHealth += 100 * $coef;
		}

		if (!$expectedHealth)
			return 100;

		return $health / $expectedHealth * 100;
	}

	/**
	 * Operation costs of service based on operation costs of its configuration items
	 * @return mixed
	 */
	public function getOperationalCosts() {
		return array_reduce($this->configurationItems, function($sum, ActiveConfigurationItem $item) {
			$sum += $item->getSpecification()->getOperationalCosts();

			return $sum;
		}, 0);
	}

	/**
	 * Get service code
	 * @return string
	 */
	public function getServiceCode() {
		return $this->service->getCode();
	}

	/**
	 * Get service ID
	 * @return int
	 */
	public function getServiceId() {
		return $this->service->getId();
	}

	/**
	 * Get active configuration item for selected configuration item
	 * @param string $code Code of selected configuration item
	 * @return ActiveConfigurationItem|null
	 */
	public function getActiveConfigurationItem($code) {
		foreach ($this->configurationItems as $activeCI) {
			if ($activeCI->getCICode() == $code)
				return $activeCI;
		}

		return null;
	}
}