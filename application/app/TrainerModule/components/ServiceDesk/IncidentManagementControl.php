<?php
/**
 * EventManagement.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 16.4.13 23:38
 */

namespace ITILSimulator\Trainer\Components\ServiceDesk;


use ITILSimulator\Entities\OperationArtifact\OperationIncident;
use ITILSimulator\Entities\Simulator\User;
use ITILSimulator\Entities\Training\KnownIssue;
use ITILSimulator\Entities\Training\Training;
use ITILSimulator\Runtime\Simulator\EntityFilter;
use ITILSimulator\Runtime\Training\PriorityEnum;
use ITILSimulator\Services\ArtifactService;
use ITILSimulator\Services\TrainingService;
use ITILSimulator\Trainer\Components\TableListControl;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\InvalidArgumentException;
use Nette\Utils\Paginator;

/**
 * Incident management control in Service Desk
 * @package ITILSimulator\Trainer\Components\ServiceDesk
 */
class IncidentManagementControl extends TableListControl
{
	#region "Properties"

	/** @var ArtifactService */
	protected $artifactService;

	/** @var TrainingService */
	protected $trainingService;

	/** @var OperationIncident */
	protected $incident;

	/** @var Training */
	protected $training;

	/** @var User */
	protected $user;

	protected $knownIssues;

	private $allowEscalate, $allowClose, $allowSolve, $allowAccept;

	#endregion

	#region "Override"

	public function __construct($parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		$this->template->STATUS_ACCEPTED = OperationIncident::STATUS_ACCEPTED;
		$this->template->STATUS_SOLVED = OperationIncident::STATUS_SOLVED;
		$this->template->STATUS_ASSIGNED = OperationIncident::STATUS_ASSIGNED;
		$this->template->STATUS_CLOSED = OperationIncident::STATUS_CLOSED;
		$this->template->STATUS_IN_PROGRESS = OperationIncident::STATUS_IN_PROGRESS;
		$this->template->STATUS_NEW = OperationIncident::STATUS_NEW;
	}

	/**
	 * Count available incidents
	 * @return mixed
	 */
	public function getTotalItemsCount()
	{
		return $this->artifactService->countAvailableIncidents($this->scenarioStep->getId(), $this->getFilter());
	}

	/**
	 * Refresh incident list
	 */
	public function invalidateList()
	{
		$this->invalidateControl('incidentsPanel');
		$this->invalidateControl('incidentsPaginator');
	}

	/**
	 * Refresh incident detail
	 */
	public function invalidateDetail()
	{
		$this->invalidateControl('detailPanelInner');
		$this->template->incident = $this->incident;
		$this->setPermittedOperations($this->incident);
	}

	protected function getFilter()
	{
		$filter = new EntityFilter();
		if (isset($this->filter['priority']) && $this->filter['priority']) {
			$filter->addWhere('priority', $this->filter['priority']);
		}
		if (isset($this->filter['status']) && $this->filter['status']) {
			$filter->addWhere('status', $this->filter['status']);
		}

		return $filter;
	}

	protected function createFilterForm(Form $form)
	{
		$form->addSelect('status', 'Status', array('' => 'status', '1' => 'new', '2' => 'archived'));
		$form->addSelect('priority', 'Priority', array('' => 'priority') + PriorityEnum::getOptions());
	}

	/**
	 * Render control
	 */
	public function render()
	{
		$this->createPaginator();

		/** @var Paginator $x */
		$x = $this->paginator->getPaginator();

		$template = $this->getCustomTemplate(__FILE__);
		$template->incidents = $this->artifactService->getAvailableIncidents($this->scenarioStep->getId(), $x->itemsPerPage, $x->offset, $this->getFilter());

		$template->solutionsAvailable = count($this->getKnownIssues(true));

		$template->render();
	}

	#endregion

	#region "Incident detail"

	/**
	 * Display incident detail
	 * @param int $id Incident ID
	 */
	public function handleDetail($id) {
		$this->loadIncident($id);
		$this->invalidateControl('detailPanel');
	}

	#endregion

	#region "Incident operations"

	/**
	 * Escalate incident
	 * @param int $id Incident ID
	 */
	public function handleEscalate($id) {
		$this->loadIncident($id);

		if ($this->allowEscalate) {
			$this->incident->logHistory(
				sprintf(
					$this->translator->translate('%s Esclated by %s'),
					date('Y-m-d H:i:s'),
					$this->user->getName()
				)
			);
			$this->incident = $this->artifactService->escalateIncident($this->incident, $this->scenarioStep->getTrainingStep());
		}

		$this->invalidateList();
		$this->invalidateDetail();
	}

	/**
	 * Apply known solution
	 * @param int $id Incident ID
	 * @param int $solution Solution ID
	 * @param bool $fix Is fix (TRUE/FALSE)
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleApplySolution($id, $solution, $fix) {
		$this->loadIncident($id);
		if ($this->allowSolve) {
			$solution = $this->trainingService->getKnownIssue($solution);
			if (!$solution || ($solution->getTraining()->getId() != $this->training->getId())) {
				throw new BadRequestException;
			}

			if ($fix) {
				$this->incident->logHistory(
					sprintf(
						$this->translator->translate('%s Applied fix %s (%s) by %s'),
						date('Y-m-d H:i:s'),
						$solution->getCode(),
						$solution->getName(),
						$this->user->getName()
					)
				);
				$this->incident = $this->artifactService->trySolveIncidentByFix($this->incident, $solution, $this->scenarioStep->getTrainingStep());

			} else {
				$this->incident->logHistory(
					sprintf(
						$this->translator->translate('%s Applied workaround %s (%s) by %s'),
						date('Y-m-d H:i:s'),
						$solution->getCode(),
						$solution->getName(),
						$this->user->getName()
					)
				);
				$this->incident = $this->artifactService->trySolveIncidentByWorkaround($this->incident, $solution, $this->scenarioStep->getTrainingStep());
			}
		}

		$this->invalidateList();
		$this->invalidateDetail();
	}

	/**
	 * Accept incident
	 * @param int $id Incident ID
	 */
	public function handleAccept($id) {
		$this->loadIncident($id);
		if ($this->allowAccept) {
			$this->incident->assign();
			$this->incident->logHistory(
				sprintf(
					$this->translator->translate('%s Accepted by %s'),
					date('Y-m-d H:i:s'),
					$this->user->getName()
				)
			);
			$this->incident = $this->artifactService->saveArtifact($this->incident, $this->scenarioStep->getTrainingStep());
		}

		$this->invalidateDetail();
		$this->invalidateList();
	}

	/**
	 * Close incident
	 * @param int $id Incident ID
	 */
	public function handleClose($id) {
		$this->loadIncident($id);
		if ($this->allowClose) {
			$this->incident->logHistory(
				sprintf(
					$this->translator->translate('%s Closed by %s'),
					date('Y-m-d H:i:s'),
					$this->user->getName()
				)
			);
			$this->incident = $this->artifactService->closeIncident($this->incident, $this->scenarioStep->getTrainingStep());

			$this->invalidateList();
			$this->invalidateDetail();
		}
	}

	#endregion

	#region "Solution form component"

	/**
	 * Create solution form component
	 * @return Form
	 */
	public function createComponentSolutionForm() {
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->getElementPrototype()->addClass('ajax');

		$form->addSelect('id', 'Solution:', $this->getKnownIssues(true));

		$form->addHidden('incident');
		if ($this->incident)
			$form['incident']->setValue($this->incident->getId());

		$form->addSubmit('save', 'Continue');

		$form->onSuccess[] = $this->onSolutionFormSuccess;

		return $form;
	}

	/**
	 * Save solution form
	 * @param Form $form
	 */
	public function onSolutionFormSuccess(Form $form) {
		$values = $form->getValues();

		$solution = $this->trainingService->getKnownIssue($values['id']);
		if (!$solution || $solution->getTrainingId() != $this->training->getId()) {
			$form->addError($this->translator->translate('Solution with id %s was not found.', $values['id']));

			return;
		}

		$this->template->code = $solution->getCode();
		$this->template->solution = $solution;
		$this->template->incidentId = $values['incident'];
		$this->invalidateControl('solutionForm');
	}

	#endregion

	#region "Get & set"

	/**
	 * @param ArtifactService $artifactsService
	 */
	public function setArtifactService($artifactsService)
	{
		$this->artifactService = $artifactsService;
	}

	/**
	 * @return ArtifactService
	 */
	public function getArtifactService()
	{
		return $this->artifactService;
	}

	/**
	 * @param \ITILSimulator\Services\TrainingService $trainingService
	 */
	public function setTrainingService($trainingService)
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
	 * @param \ITILSimulator\Entities\Training\Training $training
	 */
	public function setTraining($training)
	{
		$this->training = $training;
	}

	/**
	 * @return \ITILSimulator\Entities\Training\Training
	 */
	public function getTraining()
	{
		return $this->training;
	}

	/**
	 * @param \ITILSimulator\Entities\Simulator\User $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

	/**
	 * @return \ITILSimulator\Entities\Simulator\User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param $incident
	 */
	protected function setPermittedOperations($incident)
	{
		$this->allowClose = $this->template->allowClose = $incident->getStatus() == OperationIncident::STATUS_SOLVED;
		$this->allowSolve = $this->template->allowSolve = in_array($incident->getStatus(), array(OperationIncident::STATUS_IN_PROGRESS, OperationIncident::STATUS_ACCEPTED, OperationIncident::STATUS_ASSIGNED));
		$this->allowAccept = $this->template->allowAccept = $incident->getStatus() === OperationIncident::STATUS_NEW;
		$this->allowEscalate = $this->template->allowEscalate = $incident->getCanBeEscalated() && $incident->getLevel() < ArtifactService::SERVICE_DESK_LEVELS;
	}

	#endregion

	#region "Helpers"

	/**
	 * Load incident
	 * @param int $id Incident ID
	 * @throws \Nette\InvalidArgumentException
	 */
	protected function loadIncident($id) {
		$incident = $this->incident = $this->artifactService->getIncident($id);

		if (!$this->incident || $this->incident->getTrainingStepId() != $this->scenarioStep->getTrainingStepId()) {
			throw new InvalidArgumentException('Invalid incident ID #' . $id);
		}

		$this->template->incident = $incident;

		$this->setPermittedOperations($incident);
	}

	/**
	 * Get available known issues (errors)
	 * @param bool $asKeyValueArray TRUE to return as associative array (key = ID, value = issue name)
	 * @return KnownIssue[]|array
	 */
	protected function getKnownIssues($asKeyValueArray) {
		if (!$this->knownIssues)
			$this->knownIssues = $this->trainingService->getAvailableKnownIssues($this->scenarioStep->getTrainingId());

		$result = array();

		foreach ($this->knownIssues as $issue) {
			$result[$issue->getId()] = $asKeyValueArray ? $issue->getName() : $issue;
		}

		return $result;
	}

	#endregion
}