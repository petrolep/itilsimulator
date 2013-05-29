<?php
/**
 * DefaultPresenter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 7.4.13 14:23
 */

namespace TrainerModule;


use ITILSimulator\Base\BasePresenter;
use ITILSimulator\Entities\Training\Training;
use ITILSimulator\Services\SessionService;
use ITILSimulator\Services\TrainingService;
use ITILSimulator\Services\UserService;
use ITILSimulator\Trainer\Presenters\TrainerPresenter;
use Nette\Application\BadRequestException;

/**
 * Homepage presenter
 * @package TrainerModule
 */
class DefaultPresenter extends BasePresenter
{
	#region "Properties"

	/** @var TrainingService */
	protected $trainingService;

	#endregion

	#region "Lifecycle methods"

	public function startup() {
		parent::startup();

		if (!$this->itilConfigurator->getAllowPublicHomepage() && !$this->user->isLoggedIn()) {
			// require login
			$this->requireLogin();
		}
	}

	public function injectDefault(TrainingService $trainingService) {
		$this->trainingService = $trainingService;
	}

	#endregion

	/**
	 * Display list of available courses or redirect to dashboard.
	 */
	public function actionDefault() {
		if ($this->user->isLoggedIn()) {
			$this->redirect('dashboard:default');
		}

		$this->template->trainings = array_filter($this->trainingService->getPublishedTrainings(true), function(Training $training) {
			// display only trainings created by registered users
			return !$training->isCreatedByAnonymousUser();
		});
	}

	/**
	 * Display detail of selected training
	 * @param int $id Training ID
	 * @throws \Nette\Application\BadRequestException
	 */
	public function actionDetail($id) {
		$training = $this->trainingService->getTraining($id);
		if (!$training || !$training->isPublished()) {
			throw new BadRequestException('Training is not available.');
		}

		if (!$training->isAvailableForUser($this->user->getId())) {
			// anonymous user accessing private training
			$this->forbidden();
		}

		$this->template->training = $training;
		$this->template->canBeStarted = $training->getScenarios()->count() > 0;
	}
}