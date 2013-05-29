<?php
/**
 * ActivityContextFactory.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 15.4.13 20:09
 */

namespace ITILSimulator\Runtime\Workflow;


use ITILSimulator\Entities\Workflow\WorkflowActivity;
use ITILSimulator\Entities\Workflow\WorkflowActivitySpecification;
use ITILSimulator\Runtime\Events\EventManager;
use ITILSimulator\Runtime\RuntimeContext\WorkflowActivityRuntimeContext;

/**
 * Factory to construct ActiveWorkflowActivity. Also ensures initializing flow from one activity to another
 * @package ITILSimulator\Runtime\Workflow
 */
class ActivityContextFactory
{
	/** @var EventManager */
	protected $eventManager;

	public function __construct(EventManager $eventManager) {
		$this->eventManager = $eventManager;
	}

	/**
	 * Create new ActiveWorkflowActivity. Sets up onFinish event to process flow through the workflow from
	 * one activity to another.
	 * @param WorkflowActivity $activity
	 * @param WorkflowActivitySpecification $activitySpecification
	 * @param ActiveWorkflow $activeWorkflow
	 * @return ActiveWorkflowActivity
	 */
	public function createActiveWorkflowActivity(WorkflowActivity $activity,
	                                             WorkflowActivitySpecification $activitySpecification,
	                                             ActiveWorkflow $activeWorkflow) {

		$activeActivity = new ActiveWorkflowActivity($activity, $activitySpecification);
		$activeActivity->onFinish[] = function(ActiveWorkflowActivity $activity) use($activeWorkflow) {
			// start next activities
			$nextActivities = $activity->getWorkflowActivity()->getNextActivities();

			/** @var $activeActivities ActiveWorkflowActivity[] */
			$activeActivities = array();
			foreach ($activeWorkflow->getActivities() as $availableActivity) {
				$activeActivities[$availableActivity->getWorkflowActivity()->getId()] = $availableActivity;
			}

			// find available ActiveWorkflowActivity for next activity and start it
			foreach ($nextActivities as $nextActivity) {
				if (isset($activeActivities[$nextActivity->getId()])) {
					$activeActivities[$nextActivity->getId()]->start();
				}
			}

			// stop previous activities
			$previousActivities = $activity->getWorkflowActivity()->getPreviousActivities();
			foreach ($previousActivities as $previousActivity) {
				if (!isset($activeActivities[$previousActivity->getId()]))
					continue;

				foreach ($previousActivity->getNextActivities() as $nextActivity) {
					if (!isset($activeActivities[$nextActivity->getId()]))
						continue;

					$runningActivity = $activeActivities[$nextActivity->getId()];
					if ($runningActivity->isRunning() && $runningActivity->getWorkflowActivityId() != $activity->getWorkflowActivityId()) {
						$runningActivity->cancel();
					}
				}
			}
		};

		$runtimeContext = new WorkflowActivityRuntimeContext($activeActivity, $this->eventManager);
		$activeActivity->setRuntimeContext($runtimeContext);

		return $activeActivity;
	}
}