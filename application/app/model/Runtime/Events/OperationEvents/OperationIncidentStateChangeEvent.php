<?php
/**
 * OperationIncidentStateChangeEvent.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 23.4.13 23:34
 */

namespace ITILSimulator\Runtime\Events;


/**
 * EventManager event about operation incident state change
 * @package ITILSimulator\Runtime\Events
 */
class OperationIncidentStateChangeEvent extends Event
{
	/** @var string */
	protected $referenceNumber;

	/** @var int */
	protected $newStatus;

	public function __construct($referenceNumber, $newStatus)
	{
		$this->referenceNumber = $referenceNumber;
		$this->newStatus = $newStatus;
	}

	public function setNewStatus($newStatus)
	{
		$this->newStatus = $newStatus;
	}

	public function getNewStatus()
	{
		return $this->newStatus;
	}

	public function setReferenceNumber($referenceNumber)
	{
		$this->referenceNumber = $referenceNumber;
	}

	public function getReferenceNumber()
	{
		return $this->referenceNumber;
	}


}