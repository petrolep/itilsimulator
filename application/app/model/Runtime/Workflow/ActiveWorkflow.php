<?php
namespace ITILSimulator\Runtime\Workflow;

use ITILSimulator\Entities\Workflow\Activities\FinishActivity;
use ITILSimulator\Entities\Workflow\Activities\StartActivity;
use ITILSimulator\Entities\Workflow\Workflow;
use Nette\Diagnostics\Debugger;
use Nette\InvalidStateException;
use Nette\Object;

/**
 * Active workflow holding reference of workflow and active workflow activities
 * @package ITILSimulator\Runtime\Workflow
 */
class ActiveWorkflow extends Object
{
	/** @var Workflow */
	protected $workflow;

	/** @var ActiveWorkflowActivity[] */
	public $activities = array();

	public function __construct(Workflow $workflow) {
		$this->workflow = $workflow;
	}

	public function addWorkflowActivity(ActiveWorkflowActivity $workflowActivity) {
		$this->activities[] = $workflowActivity;
	}

	/**
	 * Whether the workflow is running
	 * @return bool
	 */
	public function isRunning() {
		return count($this->getRunningActivities()) > 0;
	}

	/**
	 * Whether the workflow has finished
	 * @return bool
	 */
	public function isFinished() {
		$finishActivities = $this->getFinishActivities();
		foreach ($finishActivities as $activity) {
			// any finished finish activity finishes whole workflow
			if ($activity->isFinished())
				return true;
		}

		return false;
	}

	/**
	 * Whether the workflow is waiting to be started
	 * @return bool
	 */
	public function isWaiting() {
		$startActivity = $this->getStartActivity();
		if (!$startActivity)
			return false;

		return $startActivity->getSpecification()->isWaiting();
	}

	/**
	 * Init workflow and its activities after being constructed (restore state etc.)
	 */
	public function init()
	{
		foreach($this->activities as $activity) {
			$activity->init();
		}
	}

	/**
	 * Start workflow
	 * @throws \Nette\InvalidStateException
	 */
	public function start() {
		if ($this->isRunning()) {
			throw new InvalidStateException('Workflow already running.');
		}

		$startActivity = $this->getStartActivity();
		if (!$startActivity) {
			throw new InvalidStateException('No start activity available in workflow #' . $this->workflow->getId());
		}

		$startActivity->start();
	}

	/**
	 * Get running activities
	 * @return ActiveWorkflowActivity[]
	 */
	public function getRunningActivities() {
		$abc = array_filter(
			$this->activities,
			function($el) {
				/** @var $el ActiveWorkflowActivity */
				return $el->getSpecification()->getState() === WorkflowActivityStateEnum::RUNNING;
			}
		);

		return $abc;
	}

	/**
	 * Get all workflow activities
	 * @return array|ActiveWorkflowActivity[]
	 */
	public function getActivities() {
		return $this->activities;
	}

	/**
	 * Get workflow
	 * @return \ITILSimulator\Entities\Workflow\Workflow
	 */
	public function getWorkflow()
	{
		return $this->workflow;
	}

	#region "Helper methods"

	/**
	 * Return start activity
	 * @return ActiveWorkflowActivity|null
	 */
	protected function getStartActivity() {
		/** @var $startActivity ActiveWorkflowActivity[] */
		$startActivity = array_filter(
			$this->activities,
			function($el) {
				/** @var $el ActiveWorkflowActivity */
				return ($el->getWorkflowActivity() instanceof StartActivity);
			}
		);

		if (!$startActivity)
			return null;

		return $startActivity[0];
	}

	/**
	 * Return finish activities
	 * @return ActiveWorkflowActivity[]
	 */
	protected function getFinishActivities() {
		/** @var $finishActivities ActiveWorkflowActivity[] */
		$finishActivities = array_filter(
			$this->activities,
			function($el) {
				/** @var $el ActiveWorkflowActivity */
				return ($el->getWorkflowActivity() instanceof FinishActivity);
			}
		);

		return $finishActivities;
	}

	#endregion
}
