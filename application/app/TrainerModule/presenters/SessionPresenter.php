<?php
/**
 * SessionPresenter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 22.4.13 21:57
 */

namespace TrainerModule;


use ITILSimulator\Entities\Session\Session;
use ITILSimulator\Entities\Session\TrainingStep;
use ITILSimulator\Entities\Simulator\Role;
use ITILSimulator\Entities\Training\Training;
use ITILSimulator\Runtime\Simulator\RoleEnum;
use ITILSimulator\Services\TrainingService;
use ITILSimulator\Trainer\Presenters\TrainerPresenter;
use Nette\Application\BadRequestException;
use Nette\Diagnostics\Debugger;

/**
 * List and detail of user`s sessions (attended trainings and their results)
 * @package TrainerModule
 */
class SessionPresenter extends TrainerPresenter
{
	#region "Properties"

	/** @persistent */
	public $id;

	/** @var Session */
	protected $selectedSession;

	/** @var Training */
	protected $training;

	/** @var TrainingService */
	protected $trainingService;

	#endregion

	#region "Lifecycle methods"

	public function startup()
	{
		parent::startup();

		if (!$this->user->isInRole(RoleEnum::STUDENT) && !$this->user->isInRole(RoleEnum::ADMIN)) {
			$this->forbidden();
		}

		$selectedSession = $this->sessionService->getSession($this->id);
		if (!$selectedSession || $selectedSession->getUser()->getId() != $this->user->getId()->getId()) {
			throw new BadRequestException('Session is not available.');
		}

		$this->selectedSession = $this->template->session = $selectedSession;
	}

	public function inject(TrainingService $trainingService)
	{
		$this->trainingService = $trainingService;
	}

	#endregion

	/**
	 * List of attended trainings
	 */
	public function actionDefault($id)
	{
		$this->training = $this->template->training = $this->selectedSession->getTraining();

		/** @var TrainingStep[] $trainingStepsArray */
		$trainingStepsArray = $this->selectedSession->getTrainingSteps()->toArray();

		/** @var TrainingStep[] $startedScenarios */
		$startedScenarios = array();
		foreach ($trainingStepsArray as $trainingStep) {
			$startedScenarios[$trainingStep->getScenarioId()] = $trainingStep;
		}
		$this->template->startedScenarios = $startedScenarios;

		// check which scenarios are finished and which are not
		if (!$this->selectedSession->isFinished() && count($startedScenarios) == count($this->training->getScenarios())) {
			// finished all scenarios?
			$this->tryFinishSession($startedScenarios);
		}

		if ($this->selectedSession->isFinished()) {
			$this->template->showFinishedTraining = true;
		}

		if ($finishedScenarioId = $this->getParameter('finished')) {
			// display message that a scenario was finished
			$this->showFinishedScenarioMessage($finishedScenarioId, $trainingStepsArray);
		}

		$this->template->totalBudget = array_reduce(
			$trainingStepsArray,
			function($value, TrainingStep $step) { return $value + $step->getBudget();},
			0
		);

		$this->template->totalPoints = array_reduce(
			$trainingStepsArray,
			function($value, TrainingStep $step) { return $value + $step->getEvaluationPoints();},
			0
		);
	}

	/**
	 * Start new scenario
	 * @param int $scenarioId Scenario ID
	 * @throws \Nette\Application\BadRequestException
	 */
	public function actionStart($scenarioId)
	{
		$scenario = $this->trainingService->getScenario($scenarioId);
		if (!$scenario || !$scenario->isAvailableForUser($this->getUserIdentity())) {
			throw new BadRequestException('Scenario is not available.');
		}

		$route = $scenario->isDesign() ? 'designer:default' : 'training:default';

		foreach($this->selectedSession->getTrainingSteps() as $trainingStep) {
			if ($trainingStep->getScenarioId() == $scenarioId) {
				// training step already exists
				$this->redirect($route, array('trainingStepId' => $trainingStep->getId()));
			}
		}

		// create new training step
		$trainingStep = $this->sessionService->startNewTrainingStep($this->selectedSession, $scenario);
		$this->sessionService->commitChanges();

		$this->redirect($route, array('trainingStepId' => $trainingStep->getId()));
	}

	#region "Helpers"

	/**
	 * Try to finish session (successes if all training steps have been finished).
	 * @param $startedScenarios
	 * @return mixed
	 */
	protected function tryFinishSession($startedScenarios)
	{
		$isFinished = true;
		foreach ($startedScenarios as $trainingStep) {
			if (!$trainingStep->isFinished()) {
				$isFinished = false;
				break;
			}
		}

		if ($isFinished) {
			// finished all scenarios -> finish the session as well
			$this->sessionService->finishSession($this->selectedSession);
		}
	}

	/**
	 * Display message about finishing a scenario
	 * @param $finishedScenarioId
	 * @param $trainingStepsArray
	 */
	protected function showFinishedScenarioMessage($finishedScenarioId, $trainingStepsArray)
	{
		$scenario = $this->training->getScenario($finishedScenarioId);
		if ($scenario) {
			$this->template->showFinishedScenario = true;
			$this->template->finishedScenario = $scenario;

			$trainingStep = array_filter($trainingStepsArray, function (TrainingStep $step) use ($finishedScenarioId) {
				return $step->getScenarioId() == $finishedScenarioId;
			});

			$this->template->results = $trainingStep ? reset($trainingStep) : NULL;
		}
	}

	#endregion
}