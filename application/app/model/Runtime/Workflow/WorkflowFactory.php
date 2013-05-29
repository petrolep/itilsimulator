<?php
/**
 * WorkflowFactory.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 3.5.13 18:52
 */

namespace ITILSimulator\Runtime\Workflow;


use ITILSimulator\Base\Position;
use ITILSimulator\Entities\Training\Scenario;
use ITILSimulator\Entities\Workflow\Activities\FinishActivity;
use ITILSimulator\Entities\Workflow\Activities\FlowActivity;
use ITILSimulator\Entities\Workflow\Activities\StartActivity;
use ITILSimulator\Entities\Workflow\ActivityMetadata;
use ITILSimulator\Entities\Workflow\Workflow;
use ITILSimulator\Entities\Workflow\WorkflowActivity;
use Nette\Object;

/**
 * Factory to create a new workflow.
 * @package ITILSimulator\Runtime\Workflow
 */
class WorkflowFactory extends Object
{
	/**
	 * Create and initialize new workflow (creates one Start and Finish activity connected with Flow activity)
	 * @param string $name
	 * @param Scenario $scenario
	 * @return Workflow
	 */
	public function makeWorkflow($name, Scenario $scenario)
	{
		$workflow = new Workflow();
		$workflow->setScenario($scenario);
		$workflow->setName($name);

		$startActivity = $this->createStartActivity($workflow);
		$finishActivity = $this->createFinishActivity($workflow);
		$flowActivity = $this->createFlowActivity($startActivity, $finishActivity, $workflow);

		$activities = $workflow->getWorkflowActivities();
		$activities->add($startActivity);
		$activities->add($finishActivity);
		$activities->add($flowActivity);

		return $workflow;
	}

	/**
	 * Create Start activity and set its default position
	 * @param Workflow $workflow
	 * @return StartActivity
	 */
	protected function createStartActivity(Workflow $workflow)
	{
		$startActivity = new StartActivity();
		$startActivity->setWorkflow($workflow);
		$metadata = new ActivityMetadata();
		$metadata->setPosition(new Position(100, 100));
		$startActivity->setMetadata($metadata);

		return $startActivity;
	}

	/**
	 * Create Finish activity and set its default position
	 * @param Workflow $workflow
	 * @return FinishActivity
	 */
	protected function createFinishActivity(Workflow $workflow)
	{
		$finishActivity = new FinishActivity();
		$finishActivity->setWorkflow($workflow);
		$metadata = new ActivityMetadata();
		$metadata->setPosition(new Position(500, 100));
		$finishActivity->setMetadata($metadata);

		return $finishActivity;
	}

	/**
	 * Create Flow activity and connect startActivity and endActivity
	 * @param WorkflowActivity $startActivity
	 * @param WorkflowActivity $endActivity
	 * @param Workflow $workflow
	 * @return FlowActivity
	 */
	protected function createFlowActivity(WorkflowActivity $startActivity, WorkflowActivity $endActivity, Workflow $workflow)
	{
		$flowActivity = new FlowActivity();
		$flowActivity->setWorkflow($workflow);
		$startActivity->getNextActivities()->add($flowActivity);
		$flowActivity->getNextActivities()->add($endActivity);

		return $flowActivity;
	}
}