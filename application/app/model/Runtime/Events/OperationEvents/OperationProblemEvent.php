<?php
/**
 * IncidentEvent.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 16.4.13 18:52
 */

namespace ITILSimulator\Runtime\Events;

use ITILSimulator\Entities\OperationArtifact\OperationProblem;

/**
 * EventManager event about OperationProblem
 * @package ITILSimulator\Runtime\Events
 */
class OperationProblemEvent extends Event
{
	/** @var OperationProblem */
	protected $operationProblem;

	/**
	 * @param \ITILSimulator\Entities\OperationArtifact\OperationProblem $problem
	 */
	public function setOperationProblem($problem)
	{
		$this->operationProblem = $problem;
	}

	/**
	 * @return \ITILSimulator\Entities\OperationArtifact\OperationProblem
	 */
	public function getOperationProblem()
	{
		return $this->operationProblem;
	}


}