<?php
/**
 * OperationIncidentSolveEvent.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 4.5.13 16:13
 */

namespace ITILSimulator\Runtime\Events;


/**
 * EventManager event about known error solution application to an operation incident
 * @package ITILSimulator\Runtime\Events
 */
class OperationIncidentSolutionAppliedEvent extends OperationIncidentEvent
{
	protected $solutionId;

	protected $cost = 0.0;

	public function setSolutionId($solutionId)
	{
		$this->solutionId = $solutionId;
	}

	public function getSolutionId()
	{
		return $this->solutionId;
	}

	public function setCost($cost)
	{
		$this->cost = $cost;
	}

	public function getCost()
	{
		return $this->cost;
	}


}