<?php
/**
 * FlowActivity.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 18.4.13 19:37
 */

namespace ITILSimulator\Entities\Workflow\Activities;


use ITILSimulator\Entities\Workflow\WorkflowActivity;
use ITILSimulator\Runtime\RuntimeContext\WorkflowActivityRuntimeContext;

/**
 * Flow activity (Doctrine entity). Connects other activities.
 * Inherits WorkflowActivity.
 * @Entity
 */
class FlowActivity extends WorkflowActivity
{
	/**
	 * @inheritdoc
	 */
	public function onStart(WorkflowActivityRuntimeContext $context) {
		parent::onStart($context);

		if (!$this->onEvent && !$this->onStart) {
			// no condition, finish immediately
			$context->execute(WorkflowActivityRuntimeContext::FINISH_COMMAND, null);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function onRestore(WorkflowActivityRuntimeContext $context) {
		$code = $this->onEvent ? $this->onEvent : WorkflowActivityRuntimeContext::FINISH_COMMAND;

		return $context->execute($code, null);
	}

	/**
	 * Return source (from which the flow starts)
	 * @return WorkflowActivity
	 */
	public function getSource()
	{
		return $this->getPreviousActivities()->first();
	}

	/**
	 * @return int
	 */
	public function getSourceId()
	{
		$s = $this->getSource();
		return $s ? $this->getSource()->getId() : 0;
	}

	/**
	 * Set flow source
	 * @param WorkflowActivity $activity
	 */
	public function setSource(WorkflowActivity $activity)
	{
		$source = $this->getSource();
		if ($source)
			$source->removeNextActivity($this);

		$this->previousActivities->clear();
		$this->previousActivities->add($activity);

		$activity->addNextActivity($this);
	}

	/**
	 * Remove flow from its source
	 */
	public function removeFromSource() {
		$this->getSource()->removeNextActivity($this);
	}

	/**
	 * Return target (where the flow ends)
	 * @return WorkflowActivity
	 */
	public function getTarget()
	{
		return $this->getNextActivities()->first();
	}

	/**
	 * @return int
	 */
	public function getTargetId()
	{
		$t = $this->getTarget();
		return $t ? $t->getId() : 0;
	}

	/**
	 * Set target (where the flow ends)
	 * @param WorkflowActivity $activity
	 */
	public function setTarget(WorkflowActivity $activity)
	{
		$target = $this->getTarget();
		if ($target)
			$target->removePreviousActivity($this);

		$this->nextActivities->clear();
		$this->nextActivities->add($activity);

		$activity->addPreviousActivity($this);
	}
  
	public function __toString() {
		return sprintf('[FlowActivity] #%d', $this->id);
	}
}