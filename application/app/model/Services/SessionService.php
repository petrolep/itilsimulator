<?php
/**
 * SessionService.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 10.4.13 22:42
 */

namespace ITILSimulator\Services;


use ITILSimulator\Runtime\Events\EventManager;
use ITILSimulator\Runtime\Session\ActiveSession;
use ITILSimulator\Runtime\Training\ActiveConfigurationItem;
use ITILSimulator\Runtime\Training\ActiveService;
use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Entities\Session\Session;
use ITILSimulator\Entities\Session\TrainingStep;
use ITILSimulator\Entities\Simulator\User;
use ITILSimulator\Entities\Training\Training;
use ITILSimulator\Entities\Training\Scenario;
use ITILSimulator\Entities\Training\ServiceSpecification;
use ITILSimulator\Repositories\Session\SessionRepository;
use ITILSimulator\Repositories\Session\TrainingStepRepository;
use ITILSimulator\Runtime\RuntimeContext\ConfigurationItemRuntimeContext;
use ITILSimulator\Runtime\Workflow\ActiveWorkflow;
use ITILSimulator\Runtime\Workflow\ActivityContextFactory;
use Nette\InvalidArgumentException;

/**
 * Session service. Handles Session, Training Step and Scenario Step tasks.
 * @package ITILSimulator\Services
 */
class SessionService implements ITransactionService
{
	#region "Properties"

	/** @var \ITILSimulator\Repositories\Session\SessionRepository */
	protected $sessionRepository;

	/** @var \ITILSimulator\Repositories\Session\TrainingStepRepository */
	protected $trainingStepRepository;

	/** @var TrainingService */
	protected $trainingService;

	/** @var StateService */
	protected $stateService;

	/** @var EventManager */
	protected $eventManager;

	#endregion

	#region "Dependencies"

	/**
	 * @param \ITILSimulator\Runtime\Events\EventManager $eventManager
	 */
	public function setEventManager(EventManager $eventManager)
	{
		$this->eventManager = $eventManager;
	}

	/**
	 * @return \ITILSimulator\Runtime\Events\EventManager
	 */
	public function getEventManager()
	{
		return $this->eventManager;
	}

	/**
	 * @param \ITILSimulator\Repositories\Session\SessionRepository $sessionRepository
	 */
	public function setSessionRepository(SessionRepository $sessionRepository)
	{
		$this->sessionRepository = $sessionRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Session\SessionRepository
	 */
	public function getSessionRepository()
	{
		return $this->sessionRepository;
	}

	/**
	 * @param \ITILSimulator\Services\StateService $stateService
	 */
	public function setStateService(StateService $stateService)
	{
		$this->stateService = $stateService;
	}

	/**
	 * @return \ITILSimulator\Services\StateService
	 */
	public function getStateService()
	{
		return $this->stateService;
	}

	/**
	 * @param \ITILSimulator\Services\TrainingService $trainingService
	 */
	public function setTrainingService(TrainingService $trainingService)
	{
		$this->trainingService = $trainingService;
	}

	/**
	 * @return \ITILSimulator\Services\TrainingService
	 */
	public function getTrainingService()
	{
		return $this->trainingService;
	}

	/**
	 * @param \ITILSimulator\Repositories\Session\TrainingStepRepository $trainingStepRepository
	 */
	public function setTrainingStepRepository(TrainingStepRepository $trainingStepRepository)
	{
		$this->trainingStepRepository = $trainingStepRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Session\TrainingStepRepository
	 */
	public function getTrainingStepRepository()
	{
		return $this->trainingStepRepository;
	}

	#endregion

	#region "Public API"

	#region "Sessions"

	/**
	 * Return available sessions for user
	 * @param int $userId
	 * @return Session[]
	 */
	public function getUserActiveSessions($userId) {
		return $this->sessionRepository->getSessionsByUser($userId);
	}

	/**
	 * Return session
	 * @param int $sessionId
	 * @return Session
	 */
	public function getSession($sessionId) {
		/** @var $session Session */
		$session = $this->sessionRepository->findOneBy(array('id' => $sessionId));

		return $session;
	}

	/**
	 * Start a new session
	 * @param User $user
	 * @param Training $training
	 * @return Session
	 * @throws \Nette\InvalidArgumentException
	 */
	public function startNewSession(User $user, Training $training)
	{
		if (!$training->isAvailableForUser($user))
			throw new InvalidArgumentException('Training is not available.');

		$session = new Session($user, $training);
		$this->sessionRepository->save($session);

		return $session;
	}

	/**
	 * Finish existing session
	 * @param Session $session
	 * @return Session
	 */
	public function finishSession(Session $session)
	{
		$session->finish();
		$this->sessionRepository->save($session);

		return $session;
	}

	/**
	 * Delete an existing session
	 * @param Session $session
	 */
	public function deleteSession(Session $session)
	{
		$this->sessionRepository->remove($session);
	}

	#endregion

	#region "Training steps"

	/**
	 * Get training step
	 * @param int $trainingStepId
	 * @return TrainingStep
	 */
	public function getTrainingStep($trainingStepId) {
		/** @var $trainingStep TrainingStep */
		$trainingStep = $this->trainingStepRepository->findOneBy(array('id' => $trainingStepId));

		return $trainingStep;
	}

	/**
	 * Finish existing training step
	 * @param TrainingStep $trainingStep
	 */
	public function finishTrainingStep(TrainingStep $trainingStep) {
		$trainingStep->finish();
	}

	/**
	 * Start new training step
	 * @param Session $session
	 * @param Scenario $scenario
	 * @return TrainingStep
	 */
	public function startNewTrainingStep(Session $session, Scenario $scenario) {
		$trainingStep = new TrainingStep();
		$trainingStep->setScenario($scenario);
		$trainingStep->setSession($session);

		$this->trainingStepRepository->save($trainingStep);

		if (!$scenario->isDesign()) {
			// create first initial scenario step
			$scenarioStep = new ScenarioStep($trainingStep);
			$scenarioStep->setBudget($scenario->getInitialBudget());

			$this->trainingStepRepository->save($scenarioStep);
		}

		return $trainingStep;
	}

	/**
	 * Return active services (services with their current valid specification)
	 * @param TrainingStep $trainingStep
	 * @return \ITILSimulator\Runtime\Training\ActiveService[]
	 * @throws \Exception
	 */
	public function getActiveServices(TrainingStep $trainingStep) {
		/** @var $activeServices ActiveService[] */
		$activeServices = array();
		$services = $trainingStep->getScenario()->getServices();
		foreach ($services as $service) {
			$serviceSpecification = $this->getServiceActiveSpecification($trainingStep->getId(), $service->getId());
			if (!$serviceSpecification) {
				throw new \Exception('No service specification for training step #' . $trainingStep->getId() . ' and service #' . $service->getId());
			}

			$activeServices[] = new ActiveService($service, $serviceSpecification);
		}

		foreach($activeServices as $activeService) {
			foreach ($activeService->getService()->getConfigurationItems() as $ci) {
				$ciSpecification = $this->getConfigurationItemActiveSpecification($trainingStep->getId(), $ci->getId());
				if (!$ciSpecification) {
					throw new \Exception('No configuration item specification for training step #' . $trainingStep->getId() . ' and configuration item #' . $ci->getId());
				}

				// create runtime context
				$activeConfigurationItem = new ActiveConfigurationItem($ci, $ciSpecification);
				$runtimeContext = new ConfigurationItemRuntimeContext($activeConfigurationItem, $this->eventManager);
				$activeConfigurationItem->setRuntimeContext($runtimeContext);
				$activeConfigurationItem->init();

				// add configuration item into service
				$activeService->addConfigurationItem($activeConfigurationItem);
			}
		}

		return $activeServices;
	}

	/**
	 * Return active workflows (workflows with activities with their current valid specifications)
	 * @param TrainingStep $trainingStep
	 * @return \ITILSimulator\Runtime\Workflow\ActiveWorkflow[]
	 * @throws \Exception
	 */
	public function getActiveWorkflows(TrainingStep $trainingStep) {
		/** @var $activeWorkflows ActiveWorkflow[] */
		$activeWorkflows = array();
		$workflows = $trainingStep->getScenario()->getWorkflows();
		foreach ($workflows as $workflow) {
			$activeWorkflows[] = new ActiveWorkflow($workflow);
		}

		$activityFactory = new ActivityContextFactory($this->eventManager);

		// create workflows and load specifications for current scenario step
		foreach ($activeWorkflows as $activeWorkflow) {
			foreach ($activeWorkflow->getWorkflow()->getWorkflowActivities() as $activity) {
				$activitySpecification = $this->getWorkflowActivityActiveSpecification($trainingStep->getId(), $activity->getId());

				if (!$activitySpecification) {
					throw new \Exception('No workflow activity specification for training step #' . $trainingStep->getId() . ' and workflow activity #' . $activity->getId());
				}

				$activeWorkflowActivity = $activityFactory->createActiveWorkflowActivity($activity, $activitySpecification, $activeWorkflow);
				$activeWorkflow->addWorkflowActivity($activeWorkflowActivity);
			}
		}

		return $activeWorkflows;
	}

	/**
	 * Return current active valid specification for service
	 * @param int $trainingStepId
	 * @param int $serviceId
	 * @return ServiceSpecification
	 */
	public function getServiceActiveSpecification($trainingStepId, $serviceId) {
		$serviceSpecificationId = $this->trainingStepRepository->getActiveServiceSpecificationId($trainingStepId, $serviceId);
		if ($serviceSpecificationId) {
			return $this->stateService->getServiceSpecification($serviceSpecificationId);
		}

		return $this->stateService->getServiceDefaultSpecification($serviceId);
	}

	/**
	 * Return current active valid specification for configuration item
	 * @param int $trainingStepId
	 * @param int $configurationItemId
	 * @return \ITILSimulator\Entities\Training\ConfigurationItemSpecification
	 */
	public function getConfigurationItemActiveSpecification($trainingStepId, $configurationItemId) {
		$ciSpecificationId = $this->trainingStepRepository->getActiveConfigurationItemSpecificationId($trainingStepId, $configurationItemId);
		if ($ciSpecificationId) {
			return $this->stateService->getConfigurationItemSpecification($ciSpecificationId);
		}

		return $this->stateService->getConfigurationItemDefaultSpecification($configurationItemId);
	}

	/**
	 * Return current active valid specification for workflow activity
	 * @param int $trainingStepId
	 * @param int $workflowActivityId
	 * @return \ITILSimulator\Entities\Workflow\WorkflowActivitySpecification
	 */
	public function getWorkflowActivityActiveSpecification($trainingStepId, $workflowActivityId) {
		$activitySpecificationId = $this->trainingStepRepository->getActiveWorkflowActivitySpecificationId($trainingStepId, $workflowActivityId);
		if ($activitySpecificationId) {
			return $this->stateService->getWorkflowActivitySpecification($activitySpecificationId);
		}

		return $this->stateService->getWorkflowActivityDefaultSpecification($workflowActivityId);
	}

	#endregion

	#region "Scenario steps"

	/**
	 * Save state of scenario step. If the state was modified, new scenario step is created and all changes
	 * objects are duplicated, updated and assigned to the new scenario step.
	 * Commits changes to the database.
	 * @param ActiveSession $activeSession
	 */
	public function changeScenarioState(ActiveSession $activeSession) {
		$trainingStep = $activeSession->getTrainingStep();
		$scenarioStep = $trainingStep->getLastValidScenarioStep();

		$services = $activeSession->getActiveServices();
		$workflows = $activeSession->getActiveWorkflows();

		$toUpdate = array();
		$toUpdateConfigurationItems = array();
		$toUpdateWorkflowActivities = array();

		// services changes
		foreach($services as $service) {
			foreach($service->getConfigurationItems() as $ci) {
				if ($ci->isSpecificationChanged()) {
					// CI specification changed, save the changed specification
					$newSpecification = $ci->getSpecification();
					$newSpecification->setIsDefault(false); // cloned specification can not be default
					$this->trainingStepRepository->save($newSpecification);
					$toUpdateConfigurationItems[] = $newSpecification;
				}
			}

			if (! $service->isSpecificationChanged())
				continue;

			$newSpecification = $service->getSpecification();
			$this->trainingStepRepository->save($newSpecification);
			$toUpdate[] = $newSpecification;
		}

		// workflows changes
		foreach($workflows as $workflow) {
			foreach ($workflow->getActivities() as $activity) {
				if ($activity->isSpecificationChanged()) {
					// activity specification changed, save the changed specification
					$newSpecification = $activity->getSpecification();
					$newSpecification->setDefault(false); // cloned specification can not be default
					$this->trainingStepRepository->save($newSpecification);
					$toUpdateWorkflowActivities[] = $newSpecification;
				}
			}
		}

		if (!$toUpdate && !$toUpdateConfigurationItems && !$toUpdateWorkflowActivities) {
			// no configuration change, do not create new scenario step
			$scenarioStep->setLastActivityDate(new \DateTime());
			$this->trainingStepRepository->save($scenarioStep);

			return;
		}

		// configuration change, create new scenario step
		$ss = new ScenarioStep($trainingStep);
		if ($toUpdate) {
			$ss->setServicesSpecifications($toUpdate);
		}
		if ($toUpdateConfigurationItems) {
			$ss->setConfigurationItemsSpecifications($toUpdateConfigurationItems);
		}
		if($toUpdateWorkflowActivities) {
			$ss->setWorkflowActivitiesSpecifications($toUpdateWorkflowActivities);
		}

		$ss->setEvaluationPoints($scenarioStep->getEvaluationPoints());
		$ss->setBudget($scenarioStep->getBudget());
		$ss->setServicesSettlementTime($scenarioStep->getServicesSettlementTime());

		$previousTime = $scenarioStep->getLastActivityDate();
		$ss->setInternalTime($scenarioStep->getInternalTime() + $ss->getDate()->diff($previousTime)->s);
		$ss->setLastActivityDate(new \DateTime());

		// commit new scenario step
		$this->trainingStepRepository->save($ss);
		$this->trainingStepRepository->commit();
	}

	/**
	 * Undo scenario step
	 * @param ScenarioStep $scenarioStep
	 */
	public function undoScenarioStep(ScenarioStep $scenarioStep)
	{
		$scenarioStep->undo();
	}

	/**
	 * Delete training step
	 * @param TrainingStep $trainingStep
	 */
	public function deleteTrainingStep(TrainingStep $trainingStep)
	{
		foreach ($trainingStep->getScenarioSteps() as $step)
			$this->trainingStepRepository->remove($step);

		$this->trainingStepRepository->remove($trainingStep);
	}

	/**
	 * Change evaluation of scenario step
	 * @param ScenarioStep $scenarioStep
	 * @param int $points
	 * @param float $budget
	 */
	public function addEvaluationPoints(ScenarioStep $scenarioStep, $points, $budget = 0.0) {
		$scenarioStep->setEvaluationPoints($scenarioStep->getEvaluationPoints($scenarioStep) + $points);
		if ($budget)
			$scenarioStep->setBudget($scenarioStep->getBudget() + $budget);
	}

	/**
	 * Update scenario step
	 * @param ScenarioStep $scenarioStep
	 */
	public function updateScenarioStep(ScenarioStep $scenarioStep) {
		$this->trainingStepRepository->save($scenarioStep);
	}

	/**
	 * Return history of evaluations for selected training step
	 * @param $trainingStepId
	 * @return mixed
	 */
	public function getEvaluationHistory($trainingStepId) {
		return $this->trainingStepRepository->getEvaluationHistory($trainingStepId);
	}

	#endregion

	public function commitChanges() {
		$this->sessionRepository->commit();
	}

	#endregion
}