<?php
/**
 * TrainingPresenter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 16.4.13 23:08
 */

namespace TrainerModule;

use ITILSimulator\Base\TemplateHelpers;
use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Entities\Session\TrainingStep;
use ITILSimulator\Entities\Training\Scenario;
use ITILSimulator\Entities\Training\Training;
use ITILSimulator\Runtime\Events\ConfigurationItemEvent;
use ITILSimulator\Runtime\Events\ConfigurationItemStateChangeEvent;
use ITILSimulator\Runtime\Events\ConfigurationItemStateRequestEvent;
use ITILSimulator\Runtime\Events\EvaluationEvent;
use ITILSimulator\Runtime\Events\EventTypeEnum;
use ITILSimulator\Runtime\Events\OperationEventEvent;
use ITILSimulator\Runtime\Events\OperationIncidentEvent;
use ITILSimulator\Runtime\Events\OperationIncidentStateChangeEvent;
use ITILSimulator\Runtime\Events\OperationProblemEvent;
use ITILSimulator\Runtime\Events\OperationProblemStateChangeEvent;
use ITILSimulator\Runtime\Events\WorkflowEvent;
use ITILSimulator\Runtime\ITILEnvironment;
use ITILSimulator\Runtime\Session\ActiveSession;
use ITILSimulator\Runtime\Simulator\RoleEnum;
use ITILSimulator\Runtime\Simulator\ServiceAccountant;
use ITILSimulator\Runtime\UI\UIManager;
use ITILSimulator\Runtime\Workflow\ActiveWorkflow;
use ITILSimulator\Services\ArtifactService;
use ITILSimulator\Services\TrainingService;
use ITILSimulator\Trainer\Components\Monitoring\MonitoringControl;
use ITILSimulator\Trainer\Components\ServiceCatalog\ServiceCatalogControl;
use ITILSimulator\Trainer\Components\ServiceDesk\EventManagementControl;
use ITILSimulator\Trainer\Components\ServiceDesk\IncidentManagementControl;
use ITILSimulator\Trainer\Components\ServiceDesk\KnownIssuesControl;
use ITILSimulator\Trainer\Components\ServiceDesk\ProblemManagementControl;
use ITILSimulator\Trainer\Components\Statistics\StatisticsControl;
use ITILSimulator\Trainer\Presenters\TrainerPresenter;
use Nette\Application\BadRequestException;
use Nette\Diagnostics\Debugger;
use Nette\Mail\Message;
use Nette\Utils\Html;

/**
 * Service operation presenter.
 * @package TrainerModule
 */
class TrainingPresenter extends TrainerPresenter
{
	#region "Properties"

	/** @persistent */
	public $trainingStepId;

	/** @var UIManager */
	protected $uiManager;

	/** @var TrainingService */
	protected $trainingService;

	/** @var ArtifactService */
	protected $artifactService;

	/** @var ActiveSession */
	protected $activeSession;

	/** @var Training */
	protected $training;

	/** @var TrainingStep */
	protected $trainingStep;
	/** @var Scenario */
	protected $scenario;

	/** @var ScenarioStep */
	protected $scenarioStep;

	/** @var int Current internal time */
	protected $currentTime = 0;

	#endregion

	#region "Lifecycle methods"

	public function inject(TrainingService $trainingService, ArtifactService $artifactService)
	{
		$this->trainingService = $trainingService;
		$this->artifactService = $artifactService;
	}

	public function startup() {
		parent::startup();

		if (!$this->user->isInRole(RoleEnum::STUDENT) && !$this->user->isInRole(RoleEnum::ADMIN)) {
			$this->forbidden();
		}

		if($trainingStepId = $this->getParameter('trainingStepId')) {
			$this->initializeTraining($trainingStepId);
		}

		if (!$this->activeSession) {
			$this->redirect('default:default');
		}
	}

	public function beforeRender() {
		parent::beforeRender();

		// commit changes to database
		$this->sessionService->commitChanges();

		// fill in template
		$this->template->scenario = $this->scenario;
		$this->template->training = $this->training;

		$this->template->points = $this->scenarioStep->getEvaluationPoints();
		$this->template->budget = $this->scenarioStep->getBudget();

		$this->template->internalTime = $this->currentTime;

		if ($this->isAjax() && $this->action != 'ping') {
			// invalidate ajax regions
			$this->invalidateControl('uiManager');
			$this->invalidateControl('flashes');
			$this->invalidateControl('wftool');
			$this->invalidateControl('jsData');
			if ($this->getParameter('jsdate')) {
				$this->template->requestDate = $this->getParameter('jsdate');
			}
		}
	}

	public function shutdown($response) {
		parent::shutdown($response);

		if ($this->activeSession && $this->action != 'executeRestart')
			$this->sessionService->changeScenarioState($this->activeSession);

		// commit once again
		$this->sessionService->commitChanges();
	}

	#endregion

	#region "Actions & rendering"

	/**
	 * Render ServiceOperation UI
	 * @param $trainingStepId
	 */
	public function renderDefault($trainingStepId) {
		$activities = array();
		foreach ($this->activeSession->getActiveWorkflows() as $activeWorkflow) {
			$activities = array_merge($activities, $activeWorkflow->getRunningActivities());
		}

		$this->template->runningActivities = $activities;
		$this->template->uiMessages = $this->uiManager->getMessages();
	}

	#region "Service desk components"

	/**
	 * Event management control
	 * @return EventManagementControl
	 */
	public function createComponentEventManagement() {
		$control = new EventManagementControl();
		$control->setTranslator($this->translator);
		$control->setArtifactsService($this->artifactService);
		$control->setScenarioStep($this->scenarioStep);

		return $control;
	}

	/**
	 * Incident management control
	 * @return IncidentManagementControl
	 */
	public function createComponentIncidentManagement() {
		$control = new IncidentManagementControl();
		$control->setTranslator($this->translator);
		$control->setArtifactService($this->artifactService);
		$control->setTrainingService($this->trainingService);
		$control->setScenarioStep($this->scenarioStep);
		$control->setTraining($this->training);
		$control->setUser($this->getUserIdentity());

		return $control;
	}

	/**
	 * Problem management control
	 * @return ProblemManagementControl
	 */
	public function createComponentProblemManagement() {
		$control = new ProblemManagementControl();
		$control->setTranslator($this->translator);
		$control->setArtifactsService($this->artifactService);
		$control->setScenarioStep($this->scenarioStep);
		$control->setUser($this->getUserIdentity());

		return $control;
	}

	/**
	 * Service catalog control
	 * @return ServiceCatalogControl
	 */
	public function createComponentServiceCatalog() {
		$control = new ServiceCatalogControl();
		$control->setTranslator($this->translator);
		$control->setActiveSession($this->activeSession);

		return $control;
	}

	/**
	 * Known issues (errors) control
	 * @return KnownIssuesControl
	 */
	public function createComponentKnownIssues() {
		$control = new KnownIssuesControl();
		$control->setTranslator($this->translator);
		$control->setTrainingService($this->trainingService);
		$control->setScenarioStep($this->scenarioStep);
		$control->setTraining($this->training);

		return $control;
	}

	/**
	 * Services monitoring control
	 * @return MonitoringControl
	 */
	protected function createComponentMonitoring() {
		$control = new MonitoringControl();
		$control->setActiveServices($this->activeSession->getActiveServices());
		$control->setTrainingService($this->trainingService);
		$control->setTrainingStepId($this->trainingStepId);
		$control->setHistoryTimeLimit($this->itilConfigurator->getGraphHistoryLimit());
		$control->setTranslator($this->translator);

		return $control;
	}

	protected function createComponentStatistics() {
		$control = new StatisticsControl();
		$control->setSessionService($this->sessionService);
		$control->setTrainingStepId($this->trainingStepId);
		$control->setHistoryTimeLimit($this->itilConfigurator->getGraphHistoryLimit());
		$control->setTranslator($this->translator);

		return $control;
	}

	#endregion

	#region "Ping"

	/**
	 * Ping request
	 */
	public function actionPing() {
		$eventProduced = false;
		$this->eventManager->addListener(EventTypeEnum::SERVICE_EVENT_CREATED, function() use(&$eventProduced) {
			// remember if any events were produced during request
			$eventProduced = true;
		});

		// ping all configuration items
		$services = $this->activeSession->getActiveServices();
		foreach($services as $service) {
			foreach ($service->getConfigurationItems() as $ci) {
				// ping all configuration items
				$ci->ping();
			}
		}

		// check if any specification was changed
		$isChanged = false;
		foreach ($services as $service) {
			if($service->isSpecificationChanged() || $service->isConfigurationItemsSpecificationChanged()) {
				$isChanged = true;
				break;
			}
		}

		// return JSON response
		$this->jsonResponse(array('date' => date('Y-m-d H:i:s') . ' - '. $this->currentTime, 'reloadServices' => $isChanged, 'reloadEvents' => $eventProduced));
	}

	#endregion

	#region "AJAX requests"

	/**
	 * Accept UI message
	 * @param $workflowActivityId
	 */
	public function handleAcceptMessage($workflowActivityId) {
		$this->uiManager->acceptMessage($workflowActivityId);
	}

	/**
	 * Reload Service catalog and Events panel (executed after "ping" caused some changes)
	 */
	public function handleReloadServices() {
		if ((bool)$this->getParameter('reloadServices'))
			$this->invalidateServicesPanel();

		$this->invalidateEvaluationPanel();
		$this->invalidateControl('flashes');

		if ((bool)$this->getParameter('reloadEvents')) {
			/** @var EventManagementControl $eventManagement */
			$eventManagement = $this['eventManagement'];
			$eventManagement->invalidateList();
		}
	}

	/**
	 * Load service monitoring control
	 */
	public function handleLoadMonitoring() {
		$this->invalidateControl('monitoring');
		$this->template->displayMonitoring = true;
	}

	/**
	 * Load my evaluation history control
	 */
	public function handleLoadStatistics() {
		$this->invalidateControl('statistics');
		$this->template->displayStatistics = true;
	}

	#endregion

	#region "Undo"

	/**
	 * Display available undo steps
	 */
	public function actionUndo() {
		/** @var $history ScenarioStep[] */
		$history = array();

		// get all available scenario steps and filter out only active ones
		$history = array_filter($this->activeSession->getScenarioSteps()->toArray(), function(ScenarioStep $step) {
			return !$step->isUndid();
		});

		// sort steps by date (ID) descending
		usort($history, function(ScenarioStep $a, ScenarioStep $b) {
			if ($a->getId() > $b->getId())
				return -1;

			return $a->getId() != $b->getId();
		});

		// construct HTML response - list of available steps (limit to 20)
		$stepsCount = 0;
		$ul = Html::el('ul');
		foreach ($history as $step) {
			if ($stepsCount == 20 || $stepsCount + 1 == count($history))
				break;

			$ul->create('li')
				->create('a', array(
					'href' => $this->link('executeUndo', array('id' => $step->getId())),
					'onclick' => 'return confirm(\'' . $this->translator->translate('All your progress up to this time will be lost, continue?') . '\');',
				))->setText($step->getDate()->format('Y-m-d H:i:s'));

			$stepsCount++;
		}

		$ul->create('li')
			->create('a', array(
				'href' => $this->link('executeRestart'),
				'onclick' => 'return confirm(\'' . $this->translator->translate('All your progress will be lost, continue?') . '\');'
			))->setText($this->translator->translate('restart scenario'));

		// return rendered HTML
		die($ul);
	}

	/**
	 * Restart scenario
	 */
	public function actionExecuteRestart() {
		// delete current training step and create a new one
		$this->sessionService->deleteTrainingStep($this->trainingStep);

		$this->flashInfoMessage('Scenario was restarted.');

		$this->redirect('session:start', array('id' => $this->trainingStep->getSessionId(), 'scenarioId' => $this->scenario->getId()));
	}

	/**
	 * Execute selected undo step
	 * @param int $id ScenarioStep ID to be returned to
	 * @throws \Nette\Application\BadRequestException
	 */
	public function actionExecuteUndo($id) {
		$selectedScenarioStep = null;
		foreach ($this->activeSession->getScenarioSteps() as $scenarioStep) {
			if ($scenarioStep->getId() == $id) {
				$selectedScenarioStep = $scenarioStep;
				break;
			}
		}

		if (!$selectedScenarioStep || $selectedScenarioStep->isUndid()) {
			// target scenario step not found or already undid
			throw new BadRequestException('Invalid scenario ID.');
		}

		// undo all steps up to the selected scenario step
		$stepsToUndo = array();
		foreach ($this->activeSession->getTrainingStep()->getScenarioSteps() as $scenarioStep) {
			if ($scenarioStep->getId() >= $selectedScenarioStep->getId() && !$scenarioStep->isUndid()) {
				$stepsToUndo[$scenarioStep->getId()] = $scenarioStep;
			}
		}

		krsort($stepsToUndo);

		foreach ($stepsToUndo as $scenarioStep) {
			$this->artifactService->undoScenarioStep($scenarioStep);
			$this->sessionService->undoScenarioStep($scenarioStep);
		}

		$this->flashInfoMessage('Scenario was restored.');
		$this->redirect('default');
	}

	#endregion

	#endregion

	#region "Runtime operational events registration"

	/**
	 * Register custom UI event handlers to EventManager
	 */
	protected function registerEvents()
	{
		$listeners = array(
			EventTypeEnum::ACTIVITY_INCIDENT_CREATED => 'onIncidentCreatedEvent',
			EventTypeEnum::ACTIVITY_EVALUATION_CREATED => 'onEvaluationCreatedEvent',
			EventTypeEnum::SERVICE_EVENT_CREATED => 'onEventCreatedEvent',
			EventTypeEnum::CONFIGURATION_ITEM_CHANGE => 'onConfigurationItemChangeEvent',
			EventTypeEnum::CONFIGURATION_ITEM_REQUEST => 'onConfigurationItemRequestEvent',
			EventTypeEnum::RUNTIME_CONFIGURATION_ITEM_RESTARTED => 'onConfigurationItemRestarted',
			EventTypeEnum::RUNTIME_CONFIGURATION_ITEM_REPLACED => 'onConfigurationItemReplaced',
			EventTypeEnum::ACTIVITY_INCIDENT_CHANGE => 'onIncidentChangeEvent',
			EventTypeEnum::ACTIVITY_PROBLEM_CHANGE => 'onProblemChangeEvent',
			EventTypeEnum::ACTIVITY_PROBLEM_CREATED => 'onProblemCreatedEvent',
			EventTypeEnum::WORKFLOW_FINISHED => 'onWorkflowFinishedEvent',
		);

		foreach ($listeners as $eventType => $callback) {
			$this->eventManager->addListener($eventType, array($this, $callback));
		}
	}

	/**
	 * Incident created handler
	 * @param OperationIncidentEvent $e
	 */
	public function onIncidentCreatedEvent(OperationIncidentEvent $e) {
		// persist incident
		$this->artifactService->saveArtifact($e->getOperationIncident(), $this->trainingStep);
		// refresh incidents list
		$this->invalidateIncidentsPanel();

		$this->flashInfoMessage('New incident created');
	}

	/**
	 * Event created handler
	 * @param OperationEventEvent $e
	 */
	public function onEventCreatedEvent(OperationEventEvent $e) {
		// persist event
		$this->artifactService->saveArtifact($e->getOperationEvent(), $this->trainingStep);
	}

	/**
	 * Problem created handler
	 * @param OperationProblemEvent $e
	 */
	public function onProblemCreatedEvent(OperationProblemEvent $e) {
		// persist problem
		$this->artifactService->saveArtifact($e->getOperationProblem(), $this->trainingStep);
		// refresh problems list
		$this->invalidateProblemsPanel();

		$this->flashInfoMessage('New problem created');
	}

	/**
	 * Evaluation created handler
	 * @param EvaluationEvent $e
	 */
	public function onEvaluationCreatedEvent(EvaluationEvent $e) {
		// persist evaluation
		$this->sessionService->addEvaluationPoints($this->scenarioStep, $e->getPoints(), $e->getMoney());
		// refresh evaluation panel
		$this->invalidateEvaluationPanel();
	}

	/**
	 * Configuration item replaced handler
	 * @param ConfigurationItemEvent $e
	 */
	public function onConfigurationItemReplaced(ConfigurationItemEvent $e) {
		// deduct purchase costs from current balance
		$cost = $e->getConfigurationItem()->getDefaultSpecification()->getPurchaseCosts();
		$this->sessionService->addEvaluationPoints($this->scenarioStep, 0, -$cost);

		$this->flashInfoMessage('Configuration item was replaced, cost %s', TemplateHelpers::currency($cost));

		$this->invalidateEvaluationPanel();
	}

	/**
	 * Configuration item restarted handler
	 * @param $e
	 */
	public function onConfigurationItemRestarted($e) {
		$this->flashInfoMessage('Configuration item was restarted');
	}

	/**
	 * Configuration item state changed handler
	 * @param ConfigurationItemStateChangeEvent $event
	 */
	public function onConfigurationItemChangeEvent(ConfigurationItemStateChangeEvent $event) {
		/** @var Scenario $scenario */
		$activeService = $this->activeSession->getActiveService($event->getServiceCode());
		if ($activeService) {
			$activeConfigurationItem = $activeService->getActiveConfigurationItem($event->getConfigurationItemCode());
			if ($activeConfigurationItem) {
				$specification = $activeConfigurationItem->getSpecification();
				if (isset($specification->{$event->getKey()})) {
					// system attribute
					$specification->{$event->getKey()} = $event->getValue();

				} else {
					// custom attribute
					$specification->setAttributeValue($event->getKey(), $event->getValue());
				}
			}
		}

		$this->invalidateServicesPanel();
	}

	/**
	 * Configuration item state request handler
	 * @param ConfigurationItemStateRequestEvent $event
	 */
	public function onConfigurationItemRequestEvent(ConfigurationItemStateRequestEvent $event) {
		/** @var Scenario $scenario */
		$activeService = $this->activeSession->getActiveService($event->getServiceCode());
		if ($activeService) {
			$activeConfigurationItem = $activeService->getActiveConfigurationItem($event->getConfigurationItemCode());
			if ($activeConfigurationItem) {
				$specification = $activeConfigurationItem->getSpecification();
				if (isset($specification->{$event->getKey()})) {
					// system attribute
					$event->setValue($specification->{$event->getKey()});

				} else {
					// custom attribute
					$attr = $specification->getAttribute($event->getKey());
					if($attr)
						$event->setValue($attr->getCurrentValue());
				}
			}
		}
	}

	/**
	 * Incident change handler
	 * @param OperationIncidentStateChangeEvent $event
	 */
	public function onIncidentChangeEvent(OperationIncidentStateChangeEvent $event) {
		foreach($this->artifactService->getAvailableIncidents($this->scenarioStep->getId()) as $incident) {
			if ($incident->getReferenceNumber() != $event->getReferenceNumber())
				continue;

			$incident->setStatus($event->getNewStatus());
			$this->artifactService->saveArtifact($incident, $this->trainingStep);
		}

		$this->invalidateIncidentsPanel();
	}

	/**
	 * Problem change handler
	 * @param OperationProblemStateChangeEvent $event
	 */
	public function onProblemChangeEvent(OperationProblemStateChangeEvent $event) {
		foreach($this->artifactService->getAvailableProblems($this->scenarioStep->getId()) as $problem) {
			if ($problem->getReferenceNumber() != $event->getReferenceNumber())
				continue;

			$problem->setStatus($event->getNewStatus());
			$this->artifactService->saveArtifact($problem, $this->trainingStep);
		}

		$this->invalidateProblemsPanel();
	}

	/**
	 * Workflow finished handler
	 * @param WorkflowEvent $event
	 */
	public function onWorkflowFinishedEvent(WorkflowEvent $event) {
		$isScenarioFinished = true;

		// check if it was the last running workflow in current scenario
		foreach ($this->activeSession->getActiveWorkflows() as $activeWorkflow) {
			if (!$activeWorkflow->isFinished()) {
				$isScenarioFinished = false;

				break;
			}
		}

		if ($isScenarioFinished) {
			// all workflows are finished, finish the scenario
			$this->sessionService->finishTrainingStep($this->trainingStep);
			$this->sessionService->commitChanges();

			$this->redirectFinishedTrainingStep($this->trainingStep);
		}

		//$this->flashInfoMessage('Workflow %s finished', $event->getWorkflowName());
	}

	#endregion

	#region "Workflow"

	/**
	 * Run workflow
	 * @param $workflows ActiveWorkflow[]
	 */
	protected function runWorkflow($workflows)
	{
		// attach events listener to all running activities
		$this->eventManager->addListener('*', function($e) use($workflows) {
			foreach ($workflows as $workflow) {
				foreach ($workflow->getRunningActivities() as $activity) {
					$activity->onEvent($e);
				}
			}
		});

		// attach another custom listeners
		$this->beforeWorkflow();

		// run workflow
		foreach ($workflows as $workflow) {
			$workflow->init();
		}
	}

	#endregion

	#region "Initialization"

	/**
	 * Initialize system before workflow is restored.
	 */
	protected function beforeWorkflow() {
		// register custom UI events
		$this->uiManager = new UIManager($this->eventManager, $this->translator);
	}

	/**
	 * Create ActiveSession object based on current session and training step
	 * @param TrainingStep $trainingStep
	 * @return ActiveSession
	 */
	protected function createActiveSession(TrainingStep $trainingStep) {
		return new ActiveSession(
			$trainingStep,
			$this->sessionService->getActiveServices($trainingStep),
			$this->sessionService->getActiveWorkflows($trainingStep)
		);
	}

	/**
	 * Initialize training
	 * @param $trainingStepId
	 * @throws \Nette\Application\BadRequestException
	 */
	protected function initializeTraining($trainingStepId)
	{
		$trainingStep = $this->sessionService->getTrainingStep($trainingStepId);

		if (!$trainingStep || $trainingStep->getUserId() != $this->getUserId()) {
			throw new BadRequestException('Training ' . $trainingStepId . ' can not be found for user ' . $this->getUserId() . '.');
		}

		if (!$trainingStep->isAvailableForUser($this->getUserIdentity())) {
			// training not published
			$this->forbidden();
		}

		if ($trainingStep->isFinished()) {
			// training finished, redirect to statistics
			$this->redirectFinishedTrainingStep($trainingStep);
		}

		if ($trainingStep->isDesign()) {
			// scenario is design, redirect to designer
			$this->redirect('designer:default', array('trainingStepId' => $trainingStep->getId()));
		}

		$this->initializeLocalVariables($trainingStep);
		$this->initializeTimer();

		$this->registerEvents();

		if ($this->action != 'ping') {
			// create workflow only if it is not ping request
			$workflows = $this->activeSession->getActiveWorkflows();

			$this->runWorkflow($workflows);
		}
	}

	/**
	 * Initialize local variables
	 * @param $trainingStep
	 */
	protected function initializeLocalVariables($trainingStep)
	{
		$this->activeSession = $this->createActiveSession($trainingStep);
		$this->scenario = $this->activeSession->getScenario();
		$this->training = $this->scenario->getTraining();
		$this->trainingStep = $this->activeSession->getTrainingStep();
		$this->scenarioStep = $this->trainingStep->getLastValidScenarioStep();
	}

	/**
	 * Initialize internal timer.
	 * Calculate difference from previous time and update current internal time.
	 */
	protected function initializeTimer()
	{
		$previousTime = $this->scenarioStep->getLastActivityDate();
		$now = new \DateTime();

		// difference from last request
		$timeDifference = $now->diff($previousTime)->s;
		if ($timeDifference > $this->itilConfigurator->getScenarioStepTimeout()) {
			// scenario was interrupted (user left the scenario and came back later)
			// do not update the internal time
			$this->currentTime = $this->scenarioStep->getInternalTime();

		} else {
			// update internal time
			$this->currentTime = $this->scenarioStep->getInternalTime() + $timeDifference;
			$this->scenarioStep->setInternalTime($this->currentTime);
		}

		// use singleton helper to store current time
		ITILEnvironment::getInstance()->setInternalTime($this->currentTime);

		$this->runServiceAccountant();
	}

	/**
	 * Run service accountant (calculates incomes / expenses for service operation)
	 */
	protected function runServiceAccountant()
	{
		$accountant = new ServiceAccountant($this->activeSession, $this->currentTime, $this->itilConfigurator->getAccountingInterval());
		if ($change = $accountant->run()) {
			$this->flashInfoMessage('New bank statement -- incomes %s, expenses %s', TemplateHelpers::currency($change->getIncome()), TemplateHelpers::currency($change->getExpenses()));
			$this->invalidateEvaluationPanel();
		}
	}


	#endregion

	#region "AJAX helpers"

	protected function invalidateIncidentsPanel()
	{
		/** @var IncidentManagementControl $incidentManagement */
		$incidentManagement = $this['incidentManagement'];
		$incidentManagement->invalidateList();
	}

	protected function invalidateProblemsPanel()
	{
		/** @var ProblemManagementControl $problemManagement */
		$problemManagement = $this['problemManagement'];
		$problemManagement->invalidateList();
	}

	protected function invalidateServicesPanel()
	{
		/** @var ServiceCatalogControl $catalog */
		$catalog = $this['serviceCatalog'];
		$catalog->invalidateServicesPanel();
		//$this->invalidateControl('serviceCatalog');
	}

	protected function invalidateEvaluationPanel()
	{
		$this->invalidateControl('evaluationData');
	}

	#endregion

}