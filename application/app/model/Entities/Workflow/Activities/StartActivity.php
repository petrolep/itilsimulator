<?php
/**
 * StartActivity.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 17:39
 */

namespace ITILSimulator\Entities\Workflow\Activities;

use ITILSimulator\Entities\Workflow\WorkflowActivity;
use ITILSimulator\Runtime\Events\Event;
use ITILSimulator\Runtime\Events\EventTypeEnum;
use ITILSimulator\Runtime\RuntimeContext\WorkflowActivityRuntimeContext;
use ITILSimulator\Runtime\Workflow\WorkflowActivityStateEnum;

/**
 * Start activity (Doctrine entity). Starts the workflow.
 * Inherits WorkflowActivity.
 * @Entity
 */
class StartActivity extends WorkflowActivity
{
	/**
	 * @inheritdoc
	 */
	public function onRestore(WorkflowActivityRuntimeContext $context) {
		$this->onStart($context);
	}

	/**
	 * @inheritdoc
	 */
	public function onStart(WorkflowActivityRuntimeContext $context) {
		$context->execute(WorkflowActivityRuntimeContext::FINISH_COMMAND, null);

		parent::onStart($context);
	}

	public function __toString() {
		return sprintf('[StartActivity] #%d: %s', $this->id, $this->description);
	}
}