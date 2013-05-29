<?php
/**
 * WorkflowServic.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 14:14
 */

namespace ITILSimulator\Services;


use ITILSimulator\Entities\Workflow\Workflow;
use ITILSimulator\Entities\Workflow\WorkflowActivity;
use ITILSimulator\Entities\Workflow\WorkflowActivitySpecification;
use ITILSimulator\Repositories\Workflow\WorkflowActivityRepository;
use ITILSimulator\Repositories\Workflow\WorkflowActivitySpecificationRepository;
use ITILSimulator\Repositories\Workflow\WorkflowRepository;
use Nette\InvalidArgumentException;

/**
 * Workflow service. Handles workflow and workflow activities.
 * @package ITILSimulator\Services
 */
class WorkflowService implements ITransactionService
{
	#region "Properties"

	/** @var WorkflowRepository */
	protected $workflowRepository;

	/** @var WorkflowActivityRepository */
	protected $workflowActivityRepository;

	/** @var  WorkflowActivitySpecificationRepository */
	protected $workflowActivitySpecificationRepository;

	#endregion

	#region "Constructor"

	public function __construct(WorkflowRepository $workflowRepository, WorkflowActivityRepository $activityRepository,
	                            WorkflowActivitySpecificationRepository $workflowActivitySpecificationRepository) {
		$this->workflowRepository = $workflowRepository;
		$this->workflowActivityRepository = $activityRepository;
		$this->workflowActivitySpecificationRepository = $workflowActivitySpecificationRepository;
	}

	/**
	 * @param \ITILSimulator\Repositories\Workflow\WorkflowActivityRepository $workflowActivityRepository
	 */
	public function setWorkflowActivityRepository(WorkflowActivityRepository $workflowActivityRepository)
	{
		$this->workflowActivityRepository = $workflowActivityRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Workflow\WorkflowActivityRepository
	 */
	public function getWorkflowActivityRepository()
	{
		return $this->workflowActivityRepository;
	}

	/**
	 * @param \ITILSimulator\Repositories\Workflow\WorkflowActivitySpecificationRepository $workflowActivitySpecificationRepository
	 */
	public function setWorkflowActivitySpecificationRepository(WorkflowActivitySpecificationRepository $workflowActivitySpecificationRepository)
	{
		$this->workflowActivitySpecificationRepository = $workflowActivitySpecificationRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Workflow\WorkflowActivitySpecificationRepository
	 */
	public function getWorkflowActivitySpecificationRepository()
	{
		return $this->workflowActivitySpecificationRepository;
	}

	/**
	 * @param \ITILSimulator\Repositories\Workflow\WorkflowRepository $workflowRepository
	 */
	public function setWorkflowRepository(WorkflowRepository $workflowRepository)
	{
		$this->workflowRepository = $workflowRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Workflow\WorkflowRepository
	 */
	public function getWorkflowRepository()
	{
		return $this->workflowRepository;
	}



	#endregion

	#region "Public API"

	#region "Workflows"

	/**
	 * Return workflow
	 * @param int $workflowId
	 * @return Workflow
	 */
	public function getWorkflow($workflowId) {
		/** @var $workflow Workflow */
		$workflow = $this->workflowRepository->findOneBy(array('id' => $workflowId));

		return $workflow;
	}

	/**
	 * Update workflow
	 * @param Workflow $workflow
	 */
	public function updateWorkflow(Workflow $workflow) {
		$this->workflowRepository->save($workflow);
	}

	/**
	 * Delete workflow
	 * @param Workflow $workflow
	 */
	public function deleteWorkflow(Workflow $workflow) {
		$this->workflowRepository->remove($workflow);
		$this->workflowRepository->commit();
	}

	#endregion

	#region "Activities"

	/**
	 * Return workflow activity
	 * @param int $workflowActivityId
	 * @return WorkflowActivity
	 */
	public function getWorkflowActivity($workflowActivityId) {
		/** @var $activity WorkflowActivity */
		$activity = $this->workflowActivityRepository->findOneBy(array('id' => $workflowActivityId));

		return $activity;
	}

	/**
	 * Update workflow activity
	 * @param WorkflowActivity $workflowActivity
	 * @throws \Nette\InvalidArgumentException
	 */
	public function updateWorkflowActivity(WorkflowActivity $workflowActivity) {
		if (!$workflowActivity->getWorkflow())
			throw new InvalidArgumentException('Activity has no workflow assigned.');

		if (!$workflowActivity->getId()) {
			// new activity
			$specification = new WorkflowActivitySpecification($workflowActivity);
			$specification->setDefault(true);
			$this->workflowActivitySpecificationRepository->save($specification);
		}

		$this->workflowActivityRepository->save($workflowActivity);
	}

	/**
	 * Delete workflow activity
	 * @param WorkflowActivity $workflowActivity
	 */
	public function deleteWorkflowActivity(WorkflowActivity $workflowActivity) {
		$this->workflowActivityRepository->remove($workflowActivity);
		$this->workflowActivityRepository->commit();
	}

	/**
	 * Return path through a scenario (sequence of workflow activity specifications in order they were executed)
	 * @param int $sessionId Session ID
	 * @param int $scenarioId Scenario ID
	 * @return mixed
	 */
	public function getScenarioPath($sessionId, $scenarioId) {
		return $this->workflowActivitySpecificationRepository->findByScenarioPath($sessionId, $scenarioId);
	}

	#endregion

	/**
	 * Commit changes to database
	 */
	public function commitChanges() {
		$this->workflowRepository->commit();
	}

	#endregion
}