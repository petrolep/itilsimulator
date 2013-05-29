<?php
/**
 * ActiveWorkflowActivity.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 15:12
 */

namespace ITILSimulator\Runtime\Workflow;


use ITILSimulator\Entities\Workflow\WorkflowActivity;
use ITILSimulator\Entities\Workflow\WorkflowActivitySpecification;
use ITILSimulator\Runtime\Events\Event;
use ITILSimulator\Runtime\RuntimeContext\WorkflowActivityRuntimeContext;
use ITILSimulator\Runtime\Training\ISpecifiableObject;
use Nette\Object;

/**
 * Active workflow activity holding reference of a workflow activity and its current valid specification
 * @package ITILSimulator\Runtime\Workflow
 */
class ActiveWorkflowActivity extends Object implements ISpecifiableObject
{
	/** @var WorkflowActivity */
	protected $workflowActivity;

	/** @var WorkflowActivitySpecification */
	protected $workflowActivitySpecification;

	/** @var WorkflowActivitySpecification */
	protected $originalWorkflowActivitySpecification;

	/** @var WorkflowActivityRuntimeContext */
	protected $runtimeContext;

	public $onFinish = array();

	public function __construct(WorkflowActivity $workflowActivity,
	                            WorkflowActivitySpecification $workflowActivitySpecification) {
		$this->workflowActivity = $workflowActivity;
		$this->workflowActivitySpecification = clone $workflowActivitySpecification;
		$this->originalWorkflowActivitySpecification = $workflowActivitySpecification;
	}

	public function getWorkflowActivity()
	{
		return $this->workflowActivity;
	}

	public function getSpecification()
	{
		return $this->workflowActivitySpecification;
	}

	public function isSpecificationChanged()
	{
		return !$this->workflowActivitySpecification->equals($this->originalWorkflowActivitySpecification);
	}

	/**
	 * @param \ITILSimulator\Runtime\RuntimeContext\WorkflowActivityRuntimeContext $runtimeContext
	 */
	public function setRuntimeContext($runtimeContext)
	{
		$this->runtimeContext = $runtimeContext;
	}

	/**
	 * @return \ITILSimulator\Runtime\RuntimeContext\WorkflowActivityRuntimeContext
	 */
	public function getRuntimeContext()
	{
		return $this->runtimeContext;
	}

	public function getWorkflowActivityId()
	{
		return $this->workflowActivity->getId();
	}

	#region "Activity events"

	/**
	 * Start activity including executing custom behavior code "onStart"
	 */
	public function start() {
		if (!$this->isRunning()) {
			// start activity
			$this->workflowActivitySpecification->setState(WorkflowActivityStateEnum::RUNNING);

			if($this->runtimeContext)
				$this->workflowActivity->onStart($this->runtimeContext);
		}

		// activity is running, trigger onFlow event
		if($this->runtimeContext)
			$this->workflowActivity->onFlow($this->runtimeContext);
	}

	/**
	 * Finish activity including executing custom behavior code "onFinish"
	 */
	public function finish() {
		if ($this->isFinished())
			return;

		$this->workflowActivitySpecification->setState(WorkflowActivityStateEnum::FINISHED);

		if ($this->runtimeContext)
			$this->workflowActivity->onFinish($this->runtimeContext);

		if ($this->onFinish) {
			foreach($this->onFinish as $callback) {
				$callback($this);
			}
		}
	}

	/**
	 * Cancel activity including executing custom behavior code "onCancel"
	 */
	public function cancel() {
		$this->workflowActivitySpecification->setState(WorkflowActivityStateEnum::CANCELLED);

		if ($this->runtimeContext)
			$this->workflowActivity->onCancel($this->runtimeContext);
	}

	/**
	 * Execute "onFlow" custom behavior code
	 */
	public function flow() {
		if ($this->runtimeContext)
			$this->workflowActivity->onFlow($this->runtimeContext);
	}

	/**
	 * Whether the activity is running
	 * @return bool
	 */
	public function isRunning() {
		return $this->workflowActivitySpecification->isRunning();
	}

	/**
	 * Whether the activity is finished
	 * @return bool
	 */
	public function isFinished() {
		return $this->workflowActivitySpecification->isFinished();
	}

	/**
	 * Whether the activity is waiting
	 * @return bool
	 */
	public function isWaiting() {
		return $this->workflowActivitySpecification->isWaiting();
	}

	/**
	 * Execute custom "onEvent" behavior code
	 * @param Event $event
	 */
	public function onEvent(Event $event) {
		if ($this->runtimeContext) {
			$this->workflowActivity->onEvent($this->runtimeContext, $event);
		}
	}

	/**
	 * Init activity including executing "onRestore" custom behavior code
	 */
	public function init() {
		if ($this->workflowActivitySpecification->isRunning()) {
			$this->workflowActivity->onRestore($this->runtimeContext);
		}
	}

	#endregion
}