<?php
/**
 * FinishActivity.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 17:40
 */

namespace ITILSimulator\Entities\Workflow\Activities;

use ITILSimulator\Entities\Workflow\WorkflowActivity;
use ITILSimulator\Runtime\Events\Event;
use ITILSimulator\Runtime\Events\EventTypeEnum;
use ITILSimulator\Runtime\Events\WorkflowEvent;
use ITILSimulator\Runtime\RuntimeContext\WorkflowActivityRuntimeContext;
use Nette\InvalidStateException;

/**
 * Finish activity (Doctrine entity). Finishes workflow.
 * Inherits WorkflowActivity.
 * @Entity
 * @HasLifecycleCallbacks
 */
class FinishActivity extends WorkflowActivity
{
	/**
	 * Executed when the workflow activity starts. Fires WORKFLOW_FINISHED event.
	 * @param WorkflowActivityRuntimeContext $context
	 * @return mixed|null|void
	 */
	public function onStart(WorkflowActivityRuntimeContext $context)
	{
		$context->execute(WorkflowActivityRuntimeContext::FINISH_COMMAND, null);

		$event = new WorkflowEvent();
		$event->setWorkflow($this->workflow);

		$context->getEventManager()->dispatch(EventTypeEnum::WORKFLOW_FINISHED, $event);

		parent::onStart($context);
	}

	public function __toString()
	{
		return sprintf('[FinishActivity] #%d: %s', $this->id, $this->description);
	}

	/**
	 * @PrePersist
	 * @PreUpdate
	 */
	public function checkConstrains()
	{
		if ($this->nextActivities != null && $this->nextActivities->count()) {
			throw new InvalidStateException('Finish activity can not have next activity.');
		}
	}
}