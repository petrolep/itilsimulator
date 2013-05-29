<?php
/**
 * TrainerPresenter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 7.4.13 14:23
 */

namespace ITILSimulator\Trainer\Presenters;


use ITILSimulator\Base\BasePresenter;
use ITILSimulator\Entities\Session\TrainingStep;
use ITILSimulator\Runtime\Events\EventManager;
use ITILSimulator\Services\SessionService;

/**
 * Presenter for training module for Service Design and Service Operation scenarios
 * @package ITILSimulator\Trainer\Presenters
 */
abstract class TrainerPresenter extends BasePresenter
{
	/** @var SessionService */
	protected $sessionService;

	/** @var EventManager */
	protected $eventManager;

	public function injectTrainerBase(SessionService $sessionService, EventManager $eventManager) {
		$this->sessionService = $sessionService;
		$this->eventManager = $eventManager;
	}

	public function startup() {
		parent::startup();

		$this->requireLogin();
	}

	public function redirectFinishedTrainingStep(TrainingStep $trainingStep)
	{
		$this->redirect('session:default', array('id' => $trainingStep->getSession()->getId(), 'finished' => $trainingStep->getScenario()->getId()));
	}
}