<?php
/**
 * MessageEvent.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 18.4.13 22:08
 */

namespace ITILSimulator\Runtime\Events;

/**
 * EventManager event about WorkflowMessage occurrence
 * @package ITILSimulator\Runtime\Events
 */
class MessageEvent extends Event
{
	/** @var int */
	protected $workflowActivityId;

	/** @var string */
	protected $title;

	/** @var string */
	protected $messageType;

	/**
	 * @param int $workflowActivityId
	 */
	public function setWorkflowActivityId($workflowActivityId)
	{
		$this->workflowActivityId = $workflowActivityId;
	}

	/**
	 * @return int
	 */
	public function getWorkflowActivityId()
	{
		return $this->workflowActivityId;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $type
	 */
	public function setMessageType($type)
	{
		$this->messageType = $type;
	}

	/**
	 * @return string
	 */
	public function getMessageType()
	{
		return $this->messageType;
	}


}