<?php
/**
 * IncidentEvent.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 16.4.13 18:52
 */

namespace ITILSimulator\Runtime\Events;


use ITILSimulator\Entities\OperationArtifact\OperationIncident;

/**
 * EventManager event about OperationIncident
 * @package ITILSimulator\Runtime\Events
 */
class OperationIncidentEvent extends Event
{
	/** @var OperationIncident */
	protected $operationIncident;

	/**
	 * @param \ITILSimulator\Entities\OperationArtifact\OperationIncident $incident
	 */
	public function setOperationIncident($incident)
	{
		$this->operationIncident = $incident;
	}

	/**
	 * @return \ITILSimulator\Entities\OperationArtifact\OperationIncident
	 */
	public function getOperationIncident()
	{
		return $this->operationIncident;
	}


}