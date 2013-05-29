<?php
/**
 * CustomActivity.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 18.5.13 23:24
 */

namespace ITILSimulator\Entities\Workflow\Activities;


use ITILSimulator\Entities\Workflow\WorkflowActivity;
use ITILSimulator\Runtime\RuntimeContext\WorkflowActivityRuntimeContext;

/**
 * Custom workflow activity (Doctrine entity). Allows creators to define their own behavior.
 * Inherits WorkflowActivity.
 * @Entity
 */
class CustomActivity extends WorkflowActivity
{
	/**
	 * @inheritdoc
	 */
	public function onStart(WorkflowActivityRuntimeContext $context) {
		parent::onStart($context);

		$this->tryFinish($context);
	}

	/**
	 * @inheritdoc
	 */
	public function onRestore(WorkflowActivityRuntimeContext $context) {
		$this->tryFinish($context);
	}

	/**
	 * @inheritdoc
	 */
	public function onFinish(WorkflowActivityRuntimeContext $context) {
		if ($this->onFinish)
			return $context->execute($this->onFinish, null);
	}

	/**
	 * @inheritdoc
	 */
	public function onCancel(WorkflowActivityRuntimeContext $context) {
		if ($this->onCancel)
			return $context->execute($this->onCancel, null);
	}

	/**
	 * @inheritdoc
	 */
	public function onFlow(WorkflowActivityRuntimeContext $context) {
		if ($this->onFlow)
			return $context->execute($this->onFlow, null);
	}

	private function tryFinish(WorkflowActivityRuntimeContext $context) {
		if (!$this->onFlow && !$this->onEvent && !$this->onCancel && !$this->onStart) {
			// no condition, finish immediately
			$context->execute(WorkflowActivityRuntimeContext::FINISH_COMMAND, null);
		}
	}
}