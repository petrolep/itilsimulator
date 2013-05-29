<?php
/**
 * UndoEvent.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 16.4.13 22:24
 */

namespace ITILSimulator\Runtime\Events;

/**
 * EventManager event about undo step performance
 * @package ITILSimulator\Runtime\Events
 */
use ITILSimulator\Entities\Session\ScenarioStep;

class UndoEvent extends Event
{
	/** @var ScenarioStep */
	protected $scenarioStep;

	/**
	 * @param \ITILSimulator\Entities\Session\ScenarioStep $scenarioStep
	 */
	public function setScenarioStep($scenarioStep)
	{
		$this->scenarioStep = $scenarioStep;
	}

	/**
	 * @return \ITILSimulator\Entities\Session\ScenarioStep
	 */
	public function getScenarioStep()
	{
		return $this->scenarioStep;
	}


}