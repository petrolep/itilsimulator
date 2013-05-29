<?php
/**
 * ConfigurationItemEvent.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 23.4.13 19:53
 */

namespace ITILSimulator\Runtime\Events;


use ITILSimulator\Entities\Training\ConfigurationItem;

/**
 * EventManager event about ConfigurationItem
 * @package ITILSimulator\Runtime\Events
 */
class ConfigurationItemEvent extends Event
{
	#region "Properties"

	/** @var ConfigurationItem */
	protected $configurationItem;

	#endregion

	public function __construct(ConfigurationItem $configurationItem)
	{
		$this->configurationItem = $configurationItem;
	}

	#region "Get & set"

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

	#endregion
}