<?php
/**
 * EventManagement.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 16.4.13 23:38
 */

namespace ITILSimulator\Trainer\Components\ServiceDesk;


use ITILSimulator\Entities\OperationArtifact\OperationEvent;
use ITILSimulator\Entities\OperationArtifact\OperationIncident;
use ITILSimulator\Entities\OperationArtifact\OperationProblem;
use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Entities\Simulator\User;
use ITILSimulator\Runtime\Simulator\EntityFilter;
use ITILSimulator\Runtime\Training\PriorityEnum;
use ITILSimulator\Services\ArtifactService;
use ITILSimulator\Trainer\Components\TableListControl;
use ITILSimulator\Trainer\Components\TrainerControl;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Nette\InvalidArgumentException;
use Nette\Utils\Paginator;

class ProblemManagementControl extends TableListControl
{
	#region "Properties"

	/** @var ArtifactService */
	protected $artifactsService;

	/** @var OperationProblem */
	protected $problem;

	/** @var User */
	protected $user;

	#endregion

	#region "Override"

	/**
	 * Count available problems
	 * @return mixed
	 */
	public function getTotalItemsCount() {
		return $this->artifactsService->countAvailableProblems($this->scenarioStep->getId(), $this->getFilter());
	}

	/**
	 * Refresh list
	 * @return mixed|void
	 */
	public function invalidateList() {
		$this->invalidateControl('problemsPanel');
		$this->invalidateControl('problemsPaginator');
	}

	/**
	 * Refresh detail
	 */
	protected function invalidateDetail() {
		$this->invalidateControl('detailPanelInner');
		$this->template->problem = $this->problem;
	}

	/**
	 * Create filter
	 * @return EntityFilter|mixed
	 */
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

	/**
	 * Create filter form
	 * @param Form $form
	 * @return mixed|void
	 */
	protected function createFilterForm(Form $form) {
		$form->addSelect('status', 'status', array(
			'' => 'status',
			OperationProblem::STATUS_NEW => 'new',
			OperationProblem::STATUS_INVESTIGATED => 'investigated',
			OperationProblem::STATUS_RESOLVED => 'resolved',
			OperationProblem::STATUS_CLOSED => 'closed'
		));

		$form->addSelect('priority', 'priority', array('' => 'priority') + PriorityEnum::getOptions());
	}

	#endregion

	/**
	 * Display list of problems
	 */
	public function handleDefault()
	{
		$this->invalidateList();
	}

	public function handleDetail($id) {
		$this->loadProblem($id);
		$this->invalidateControl('detailPanel');
	}

	/**
	 * Render control
	 */
	public function render()
	{
		$this->createPaginator();

		$template = $this->getCustomTemplate(__FILE__);
		$template->problems = $this->getProblems();
		$template->STATUS_NEW = OperationProblem::STATUS_NEW;
		$template->STATUS_INVESTIGATED = OperationProblem::STATUS_INVESTIGATED;
		$template->STATUS_RESOLVED = OperationProblem::STATUS_RESOLVED;
		$template->STATUS_CLOSED = OperationProblem::STATUS_CLOSED;
		$template->render();
	}

	#region "Operations"

	#region "Known error request"

	/**
	 * Create new known error request
	 * @param $id
	 */
	public function handleCreateKnownError($id) {
		$this->loadProblem($id);
		if ($this->problem) {
			$this->problem->logHistory(
				sprintf(
					$this->translator->translate('%s Created Known Error record %s'),
					date('Y-m-d H:i:s'),
					$this->user->getName()
				)
			);

			$this->problem = $this->artifactsService->requestProblemsKnownErrorRecord($this->problem, $this->scenarioStep->getTrainingStep());
		}

		$this->invalidateDetail();
		$this->invalidateList();
	}

	#endregion

	#region "RFC form"

	/**
	 * Create RFC (Request For Change) form component
	 * @return Form
	 */
	public function createComponentRfcForm() {
		$form = new Form();
		$form->setTranslator($this->translator);

		$form->addText('service', 'Service code:')
			->addRule(Form::FILLED, 'Service code is required.')
			->getLabelPrototype()->addClass('required');

		$form->addText('ci', 'CI code:')
			->addRule(Form::FILLED, 'Configuration item code is required.')
			->getLabelPrototype()->addClass('required');

		$form->addHidden('problem');
		if($this->problem)
			$form['problem']->setValue($this->problem->getId());

		$form->addSubmit('save', 'Send request');

		$form->onSuccess[] = $this->onRfcFormSuccess;

		return $form;
	}

	/**
	 * Save RFC form
	 * @param Form $form
	 */
	public function onRfcFormSuccess(Form $form) {
		$values = $form->getValues();
		$this->loadProblem($values['problem']);

		if ($this->problem) {
			$this->problem->logHistory(
				sprintf(
					$this->translator->translate('%s Requested RFC %s'),
					date('Y-m-d H:i:s'),
					$this->user->getName()
				)
			);
			$this->problem = $this->artifactsService->requestProblemsRFC($this->problem, $this->scenarioStep->getTrainingStep(), $values['service'], $values['ci']);
		}

		$this->invalidateControl('rfcForm');
		$this->invalidateDetail();
		$this->invalidateList();
	}

	#endregion

	#endregion

	#region "Get & set"

	/**
	 * @param ArtifactService $artifactsService
	 */
	public function setArtifactsService($artifactsService)
	{
		$this->artifactsService = $artifactsService;
	}

	/**
	 * @return ArtifactService
	 */
	public function getArtifactsService()
	{
		return $this->artifactsService;
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

	#endregion

	#region "Helpers"

	/**
	 * Load problem
	 * @param int $id Problem ID
	 * @throws \Nette\InvalidArgumentException
	 */
	protected function loadProblem($id) {
		$this->problem = $this->artifactsService->getProblem($id);
		$this->template->problem = $this->problem;

		if (!$this->problem) {
			throw new InvalidArgumentException('Invalid problem ID #' . $id);
		}
	}

	/**
	 * Load available problems
	 * @return array
	 */
	protected function getProblems() {
		/** @var Paginator $x */
		$x = $this->paginator->getPaginator();

		return $this->artifactsService->getAvailableProblems($this->scenarioStep->getId(), $x->itemsPerPage, $x->offset, $this->getFilter());
	}

	#endregion

}