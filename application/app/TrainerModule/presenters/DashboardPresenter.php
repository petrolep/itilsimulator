<?php
/**
 * DashboardPresenter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 7.4.13 14:23
 */

namespace TrainerModule;


use ITILSimulator\Entities\Training\Training;
use ITILSimulator\Runtime\Simulator\RoleEnum;
use ITILSimulator\Services\SessionService;
use ITILSimulator\Services\TrainingService;
use ITILSimulator\Services\UserService;
use ITILSimulator\Trainer\Presenters\TrainerPresenter;
use Nette\Application\BadRequestException;

/**
 * Dashboard presenter displaying My training and other available trainings.
 * @package TrainerModule
 */
class DashboardPresenter extends TrainerPresenter
{
	#region "Properties"

	/** @var TrainingService */
	protected $trainingService;

	#endregion

	#region "Lifecycle methods"

	public function inject(TrainingService $trainingService)
	{
		$this->trainingService = $trainingService;
	}

	public function startup() {
		parent::startup();

		$this->requireLogin();

		$trainings = $this->trainingService->getPublishedTrainings(false);
		$userId = $this->getUserId();

		$this->template->sessions = $this->getUserIdentity() ? $this->sessionService->getUserActiveSessions($this->getUserId()) : array();
		// filter only public available trainings not created by anonymous users
		$this->template->trainings = array_filter($trainings, function(Training $t) use($userId) { return ($t->isPublic() && !$t->getUser()->isAnonymous()) || $t->getUserId() == $userId; });
	}

	#endregion

	/**
	 * Start new training
	 * @param int $id Training ID
	 * @throws \Nette\Application\BadRequestException
	 */
	public function actionStart($id) {
		$training = $this->trainingService->getTraining($id);
		if (!$training || !$training->isAvailableForUser($this->getUserIdentity()) || !$training->getScenarios()->count()) {
			throw new BadRequestException('Training is not available.');
		}

		// refresh user object (Doctrine does not support keeping objects in session)
		$user = $this->getDBUser();

		if (!$this->user->isInRole(RoleEnum::STUDENT) && !$this->user->isInRole(RoleEnum::ADMIN)) {
			$this->forbidden();
		}

		$session = $this->sessionService->startNewSession($user, $training);
		$this->sessionService->commitChanges();

		$this->redirect('session:default', array('id' => $session->getId()));
	}
}