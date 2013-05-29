<?php
/**
 * WorkflowEvent.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 27.4.13 20:40
 */

namespace ITILSimulator\Runtime\Events;

use ITILSimulator\Entities\Workflow\Workflow;

/**
 * EventManager event about workflow event
 * @package ITILSimulator\Runtime\Events
 */
class WorkflowEvent extends Event
{
	/** @var Workflow */
	protected $workflow;

	/**
	 * @param \ITILSimulator\Entities\Workflow\Workflow $workflow
	 */
	public function setWorkflow($workflow)
	{
		$this->workflow = $workflow;
	}

	/**
	 * @return \ITILSimulator\Entities\Workflow\Workflow
	 */
	public function getWorkflow()
	{
		return $this->workflow;
	}

	public function getWorkflowName()
	{
		return $this->workflow ? $this->workflow->getName() : '';
	}
}