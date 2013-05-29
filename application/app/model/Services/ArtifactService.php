<?php
/**
 * ArtifactService.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 21:24
 */

namespace ITILSimulator\Services;


use ITILSimulator\Base\ITILConfigurator;
use ITILSimulator\Entities\OperationArtifact\OperationArtifact;
use ITILSimulator\Entities\OperationArtifact\OperationCategory;
use ITILSimulator\Entities\OperationArtifact\OperationEvent;
use ITILSimulator\Entities\OperationArtifact\OperationIncident;
use ITILSimulator\Entities\OperationArtifact\OperationProblem;
use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Entities\Session\TrainingStep;
use ITILSimulator\Entities\Training\KnownIssue;
use ITILSimulator\Repositories\OperationArtifact\OperationCategoryRepository;
use ITILSimulator\Repositories\OperationArtifact\OperationEventRepository;
use ITILSimulator\Repositories\OperationArtifact\OperationIncidentRepository;
use ITILSimulator\Repositories\OperationArtifact\OperationProblemRepository;
use ITILSimulator\Runtime\Events\EventManager;
use ITILSimulator\Runtime\Events\EventTypeEnum;
use ITILSimulator\Runtime\Events\OperationEventEvent;
use ITILSimulator\Runtime\Events\OperationIncidentEvent;
use ITILSimulator\Runtime\Events\OperationIncidentSolutionAppliedEvent;
use ITILSimulator\Runtime\Events\OperationProblemEvent;
use ITILSimulator\Runtime\OperationArtifact\OperationArtifactPersistentUnit;
use ITILSimulator\Runtime\Simulator\EntityFilter;
use Nette\Application\Application;

/**
 * Artifacts service. Handles Operation Artifacts (events, incidents, problems) and Operation Categories.
 * @package ITILSimulator\Services
 */
class ArtifactService implements ITransactionService
{
	const SERVICE_DESK_LEVELS = 2;

	#region "Properties"

	/** @var OperationEventRepository */
	protected $eventsRepository;

	/** @var OperationIncidentRepository */
	protected $incidentsRepository;

	/** @var OperationProblemRepository */
	protected $problemsRepository;

	/** @var OperationCategoryRepository */
	protected $categoriesRepository;

	/** @var EventManager */
	protected $eventManager;

	/** @var \ITILSimulator\Base\ITILConfigurator  */
	protected $configuration;

	#endregion

	#region "Dependencies"

	/**
	 * @param \ITILSimulator\Repositories\OperationArtifact\OperationCategoryRepository $categoriesRepository
	 */
	public function setCategoriesRepository(OperationCategoryRepository $categoriesRepository)
	{
		$this->categoriesRepository = $categoriesRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\OperationArtifact\OperationCategoryRepository
	 */
	public function getCategoriesRepository()
	{
		return $this->categoriesRepository;
	}

	/**
	 * @param \ITILSimulator\Base\ITILConfigurator $configuration
	 */
	public function setConfiguration(ITILConfigurator $configuration)
	{
		$this->configuration = $configuration;
	}

	/**
	 * @return \ITILSimulator\Base\ITILConfigurator
	 */
	public function getConfiguration()
	{
		return $this->configuration;
	}

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
	 * @param \ITILSimulator\Repositories\OperationArtifact\OperationEventRepository $eventsRepository
	 */
	public function setEventsRepository(OperationEventRepository $eventsRepository)
	{
		$this->eventsRepository = $eventsRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\OperationArtifact\OperationEventRepository
	 */
	public function getEventsRepository()
	{
		return $this->eventsRepository;
	}

	/**
	 * @param \ITILSimulator\Repositories\OperationArtifact\OperationIncidentRepository $incidentsRepository
	 */
	public function setIncidentsRepository(OperationIncidentRepository $incidentsRepository)
	{
		$this->incidentsRepository = $incidentsRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\OperationArtifact\OperationIncidentRepository
	 */
	public function getIncidentsRepository()
	{
		return $this->incidentsRepository;
	}

	/**
	 * @param \ITILSimulator\Repositories\OperationArtifact\OperationProblemRepository $problemsRepository
	 */
	public function setProblemsRepository(OperationProblemRepository $problemsRepository)
	{
		$this->problemsRepository = $problemsRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\OperationArtifact\OperationProblemRepository
	 */
	public function getProblemsRepository()
	{
		return $this->problemsRepository;
	}

	#endregion

	#region "Public API"

	#region "Events"

	/**
	 * Return operation event
	 * @param int $eventId Event ID
	 * @return OperationEvent
	 */
	public function getEvent($eventId) {
		return $this->eventsRepository->findOneActive($eventId);
	}

	/**
	 * Return all available events available for selected scenario step
	 * @param int $scenarioStepId
	 * @param int $limit
	 * @param int $offset
	 * @param EntityFilter|null $filter
	 * @return OperationEvent[]
	 */
	public function getAvailableEvents($scenarioStepId, $limit = 0, $offset = 0, $filter = NULL) {
		return $this->eventsRepository->findAvailable($scenarioStepId, $limit, $offset, $filter);
	}

	/**
	 * Count all available events available for selected scenario step
	 * @param $scenarioStepId
	 * @param EntityFilter|null $filter
	 * @return int
	 */
	public function countAvailableEvents($scenarioStepId, $filter = NULL) {
		return $this->eventsRepository->countAvailable($scenarioStepId, $filter);
	}

	/**
	 * Archive an operation event. Fires RUNTIME_EVENT_ARCHIVED event.
	 * @param OperationEvent $operationEvent
	 * @param TrainingStep $trainingStep
	 */
	public function archiveEvent(OperationEvent $operationEvent, TrainingStep $trainingStep) {
		$event = new OperationEventEvent();
		$event->setOperationEvent($operationEvent);
		$event->setCode($operationEvent->getCode());
		$event->setSource($operationEvent->getSource());
		$event->setDescription($operationEvent->getDescription());

		// archive operation event
		$operationEvent->archive();

		$this->saveArtifact($operationEvent, $trainingStep);

		// dispatch event
		$this->eventManager->dispatch(EventTypeEnum::RUNTIME_EVENT_ARCHIVED, $event);
	}

	#endregion

	#region "Incidents"

	/**
	 * Return incident
	 * @param int $incidentId Incident ID
	 * @return OperationIncident
	 */
	public function getIncident($incidentId) {
		return $this->incidentsRepository->findOneActive($incidentId);
	}

	/**
	 * Return all available incidents available for selected scenario step
	 * @param int $scenarioStepId
	 * @param int $limit
	 * @param int $offset
	 * @param EntityFilter|null $filter
	 * @return \ITILSimulator\Entities\OperationArtifact\OperationIncident[]
	 */
	public function getAvailableIncidents($scenarioStepId, $limit = 0, $offset = 0, $filter = NULL) {
		return $this->incidentsRepository->findAvailable($scenarioStepId, $limit, $offset, $filter);
	}

	/**
	 * Count available incidents available for selected scenario step
	 * @param $scenarioStepId
	 * @param EntityFilter|null $filter
	 * @return int
	 */
	public function countAvailableIncidents($scenarioStepId, $filter = NULL) {
		return $this->incidentsRepository->countAvailable($scenarioStepId, $filter);
	}

	/**
	 * Accept incident. Fires RUNTIME_INCIDENT_ACCEPTED event.
	 * @param OperationIncident $operationIncident
	 * @param TrainingStep $trainingStep
	 */
	public function acceptIncident(OperationIncident $operationIncident, TrainingStep $trainingStep) {
		$event = $this->getIncidentOperationEvent($operationIncident);

		$operationIncident->setStatus(OperationIncident::STATUS_ASSIGNED);

		$this->eventManager->dispatch(EventTypeEnum::RUNTIME_INCIDENT_ACCEPTED, $event);
	}

	/**
	 * Escalate incident. Fires RUNTIME_INCIDENT_ESCALATED event.
	 * @param OperationIncident $operationIncident
	 * @param TrainingStep $trainingStep
	 * @return OperationArtifact|OperationIncident|null
	 */
	public function escalateIncident(OperationIncident $operationIncident, TrainingStep $trainingStep) {
		$event = $this->getIncidentOperationEvent($operationIncident);

		$result = $operationIncident;
		if ($operationIncident->getCanBeEscalated() && $operationIncident->getLevel() < self::SERVICE_DESK_LEVELS) {
			$operationIncident->setLevel($operationIncident->getLevel() + 1);
			$result = $this->saveArtifact($operationIncident, $trainingStep);

			$scenarioStep = $trainingStep->getLastValidScenarioStep();
			$scenarioStep->setBudget($scenarioStep->getBudget() - $this->configuration->getEscalationCost());
		}

		$this->eventManager->dispatch(EventTypeEnum::RUNTIME_INCIDENT_ESCALATED, $event);

		return $result;
	}

	/**
	 * Close incident. Fires RUNTIME_INCIDENT_CLOSED event.
	 * @param OperationIncident $operationIncident
	 * @param TrainingStep $trainingStep
	 * @return OperationArtifact|null
	 */
	public function closeIncident(OperationIncident $operationIncident, TrainingStep $trainingStep) {
		$event = $this->getIncidentOperationEvent($operationIncident);

		$operationIncident->setStatus(OperationIncident::STATUS_CLOSED);
		$result = $this->saveArtifact($operationIncident, $trainingStep);

		$this->eventManager->dispatch(EventTypeEnum::RUNTIME_INCIDENT_CLOSED, $event);

		return $result;
	}

	/**
	 * Try to solve incident by using a fix from Known Error Record. Fires RUNTIME_INCIDENT_FIX_APPLIED event.
	 * @param OperationIncident $operationIncident
	 * @param KnownIssue $solution
	 * @param TrainingStep $trainingStep
	 * @return OperationArtifact|null
	 */
	public function trySolveIncidentByFix(OperationIncident $operationIncident, KnownIssue $solution, TrainingStep $trainingStep) {
		$event = new OperationIncidentSolutionAppliedEvent();
		$event->setSolutionId($solution->getId());
		$event->setCost($solution->getFixCost());

		return $this->fixIncident($operationIncident, $solution, $trainingStep, $event, EventTypeEnum::RUNTIME_INCIDENT_FIX_APPLIED);
	}

	/**
	 * Try to solve incident by using a workaround from Known Error Record. Fires RUNTIME_INCIDENT_WORKAROUND_APPLIED event.
	 * @param OperationIncident $operationIncident
	 * @param KnownIssue $solution
	 * @param TrainingStep $trainingStep
	 * @return OperationArtifact|null
	 */
	public function trySolveIncidentByWorkaround(OperationIncident $operationIncident, KnownIssue $solution, TrainingStep $trainingStep) {
		$event = new OperationIncidentSolutionAppliedEvent();
		$event->setSolutionId($solution->getId());
		$event->setCost($solution->getWorkaroundCost());

		return $this->fixIncident($operationIncident, $solution, $trainingStep, $event, EventTypeEnum::RUNTIME_INCIDENT_WORKAROUND_APPLIED);
	}

	#endregion

	#region "Problems"

	/**
	 * Return operation problem
	 * @param int $problemId
	 * @return OperationProblem|null
	 */
	public function getProblem($problemId) {
		return $this->problemsRepository->findOneActive($problemId);
	}

	/**
	 * Return all available operation problems available for selected scenario step
	 * @param $scenarioStepId
	 * @param int $limit
	 * @param int $offset
	 * @param EntityFilter|null $filter
	 * @return OperationProblem[]
	 */
	public function getAvailableProblems($scenarioStepId, $limit = 0, $offset = 0, $filter = NULL) {
		return $this->problemsRepository->findAvailable($scenarioStepId, $limit, $offset, $filter);
	}

	/**
	 * Count all available operation problems available for selected scenario step
	 * @param int $scenarioStepId
	 * @param EntityFilter|null $filter
	 * @return mixed
	 */
	public function countAvailableProblems($scenarioStepId, $filter = NULL) {
		return $this->problemsRepository->countAvailable($scenarioStepId, $filter);
	}

	/**
	 * Request new Known Error Record for existing Operation Problem. Fires RUNTIME_PROBLEM_KNOWN_ERROR_REQUESTED event.
	 * @param OperationProblem $operationProblem
	 * @param TrainingStep $trainingStep
	 * @return OperationArtifact|null
	 */
	public function requestProblemsKnownErrorRecord(OperationProblem $operationProblem, TrainingStep $trainingStep) {
		$event = new OperationProblemEvent();
		$event->setOperationProblem($operationProblem);

		$return = $this->saveArtifact($operationProblem, $trainingStep);

		$this->eventManager->dispatch(EventTypeEnum::RUNTIME_PROBLEM_KNOWN_ERROR_REQUESTED, $event);

		return $return;
	}

	/**
	 * Request new Request For Change for existing Operation Problem. Fires RUNTIME_PROBLEM_RFC_REQUESTED event.
	 * @param OperationProblem $operationProblem
	 * @param TrainingStep $trainingStep
	 * @param string $serviceCode
	 * @param string $ciCode
	 * @return OperationArtifact|null
	 */
	public function requestProblemsRFC(OperationProblem $operationProblem, TrainingStep $trainingStep, $serviceCode, $ciCode) {
		$event = new OperationProblemEvent();
		$event->setOperationProblem($operationProblem);
		$event->setSource($operationProblem->getReferenceNumber());
		$event->setCode($serviceCode . ';' . $ciCode);

		$return = $this->saveArtifact($operationProblem, $trainingStep);

		$this->eventManager->dispatch(EventTypeEnum::RUNTIME_PROBLEM_RFC_REQUESTED, $event);

		return $return;
	}

	#endregion

	#region "Categories"

	/**
	 * Return operation category
	 * @param int $id
	 * @return OperationCategory
	 */
	public function getCategory($id) {
		return $this->categoriesRepository->findOneBy(array('id' => $id));
	}

	/**
	 * Return all available categories for selected training step
	 * @param int $trainingId
	 * @return OperationCategory[]
	 */
	public function getAvailableCategories($trainingId) {
		return $this->categoriesRepository->findBy(array('training' => $trainingId));
	}

	/**
	 * Save operation category
	 * @param OperationCategory $category
	 */
	public function saveCategory(OperationCategory $category) {
		$this->categoriesRepository->save($category);
	}

	/**
	 * Delete operation category
	 * @param OperationCategory $category
	 */
	public function deleteCategory(OperationCategory $category) {
		$this->categoriesRepository->remove($category);
	}

	#endregion

	#region "General methods"

	/**
	 * Undo scenario step events, incidents and problems
	 * (undoes only these operation artifacts, not the scenario step itself!)
	 * @param ScenarioStep $scenarioStep
	 */
	public function undoScenarioStep(ScenarioStep $scenarioStep) {
		$this->eventsRepository->undo($scenarioStep);
		$this->incidentsRepository->undo($scenarioStep);
		$this->problemsRepository->undo($scenarioStep);
	}

	/**
	 * Save operation artifact
	 * @param OperationArtifact $artifact
	 * @param TrainingStep $trainingStep Current training step (artifact will be valid from this step)
	 * @return OperationArtifact|null
	 */
	public function saveArtifact(OperationArtifact $artifact, TrainingStep $trainingStep) {
		$unit = new OperationArtifactPersistentUnit($artifact, $trainingStep->getLastValidScenarioStep());

		switch($artifact) {
			case($artifact instanceof OperationEvent):
				return $this->eventsRepository->save($unit);

			case ($artifact instanceof OperationIncident):
				return $this->incidentsRepository->save($unit);

			case ($artifact instanceof OperationProblem):
				return $this->problemsRepository->save($unit);
		}

		return NULL;
	}

	/**
	 * Commit changes to database
	 */
	public function commitChanges()
	{
		$this->eventsRepository->commit();
	}

	#endregion

	#endregion

	#region "Helper methods"

	/**
	 * @param OperationIncident $operationIncident
	 * @return OperationIncidentEvent
	 */
	protected function getIncidentOperationEvent(OperationIncident $operationIncident)
	{
		$event = new OperationIncidentEvent();
		$event->setOperationIncident($operationIncident);
		$event->setCode($operationIncident->getReferenceNumber());
		$event->setSource($operationIncident->getReferenceNumber());
		return $event;
	}

	/**
	 * Apply fix to incident. Fires either RUNTIME_INCIDENT_FIX_APPLIED or RUNTIME_INCIDENT_WORKAROUND_APPLIED event.
	 * @param OperationIncident $operationIncident
	 * @param KnownIssue $solution
	 * @param TrainingStep $trainingStep
	 * @param OperationIncidentSolutionAppliedEvent $event
	 * @param $eventType
	 * @return OperationArtifact|null|void
	 */
	private function fixIncident(OperationIncident $operationIncident, KnownIssue $solution,
	                               TrainingStep $trainingStep, OperationIncidentSolutionAppliedEvent $event, $eventType) {
		$event->setOperationIncident($operationIncident);
		$event->setCode($solution->getCode());
		$event->setSource($operationIncident->getReferenceNumber());

		$operationIncident->setStatus(OperationIncident::STATUS_IN_PROGRESS);
		$result = $this->saveArtifact($operationIncident, $trainingStep);

		$scenarioStep = $trainingStep->getLastValidScenarioStep();
		$scenarioStep->setBudget($scenarioStep->getBudget() - $event->getCost());

		$this->eventManager->dispatch($eventType, $event);

		return $result;
	}

	#endregion
}