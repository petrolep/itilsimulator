<?php
/**
 * StudentPresenter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 29.4.13 21:42
 */

namespace CreatorModule;


use Doctrine\Common\Util\Debug;
use ITILSimulator\Creator\Presenters\CreatorPresenter;
use ITILSimulator\Entities\Session\Session;
use ITILSimulator\Services\WorkflowService;
use Nette\Application\BadRequestException;

/**
 * Presenter to check students and their progress
 * @package CreatorModule
 */
class StudentPresenter extends CreatorPresenter
{
	#region "Properties"

	/** @var WorkflowService */
	protected $workflowService;

	#endregion

	#region "Lifecycle methods"

	public function inject(WorkflowService $workflowService) {
		$this->workflowService = $workflowService;
	}

	#endregion

	#region "Public methods"

	/**
	 * List of all students who attended trainings created by current creator
	 */
	public function actionDefault() {
		$this->template->students = $this->userService->getStudentsByCreator($this->getUserIdentity());
	}

	/**
	 * Detail of selected student
	 * @param int $id Student ID
	 * @throws \Nette\Application\BadRequestException
	 */
	public function actionDetail($id) {
		$user = $this->template->student = $this->userService->getStudentByCreator($id, $this->getUserIdentity());
		if (!$user) {
			throw new BadRequestException('Invalid student ID.');
		}

		$currentUserId = $this->getUserId();

		$this->template->sessions = array_filter(
			$user->getSessions()->toArray(),
			function(Session $session) use($currentUserId) {
				return $session->getTrainingCreatorUserId() == $currentUserId;
			}
		);

		$paths = array();
		foreach($this->template->sessions as $session) {
			/** @var $session Session */
			foreach($session->getTrainingSteps() as $trainingStep) {
				$scenarioId = $trainingStep->getScenarioId();
				$path = $this->workflowService->getScenarioPath($session->getId(), $scenarioId);
				$paths[$scenarioId] = $path;
			}
		}

		$this->template->paths = $paths;
	}

	#endregion
}