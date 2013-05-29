<?php
/**
 * ArchiveOperationEventEvent.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 17.4.13 11:15
 */

namespace ITILSimulator\Runtime\Events;


use ITILSimulator\Entities\OperationArtifact\OperationEvent;

/**
 * EventManager event about OperationEvent
 * @package ITILSimulator\Runtime\Events
 */
class OperationEventEvent extends Event
{
	/** @var OperationEvent */
	protected $operationEvent;

	/**
	 * @param \ITILSimulator\Entities\OperationArtifact\OperationEvent $operationEvent
	 */
	public function setOperationEvent($operationEvent)
	{
		$this->operationEvent = $operationEvent;
	}

	/**
	 * @return \ITILSimulator\Entities\OperationArtifact\OperationEvent
	 */
	public function getOperationEvent()
	{
		return $this->operationEvent;
	}
}