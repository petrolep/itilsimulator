<?php
/**
 * StatisticsControl.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 16:29
 */

namespace ITILSimulator\Trainer\Components\Statistics;


use ITILSimulator\Services\SessionService;
use ITILSimulator\Trainer\Components\TrainerControl;

/**
 * History of users evaluation and points
 * @package ITILSimulator\Trainer\Components\Statistics
 */
class StatisticsControl extends TrainerControl
{
	#region "Properties"

	/** @persistent */
	public $type = 'budget';

	/** @var SessionService */
	protected $sessionService;

	/** @var int */
	protected $trainingStepId;

	/** @var int */
	protected $historyTimeLimit = 1000;

	#endregion

	/**
	 * Refresh graph
	 */
	public function handleReload() {
		$this->invalidateControl('statistics');
	}

	public function render()
	{
		$template = $this->getCustomTemplate(__FILE__);

		list($budget, $points) = $this->getData();
		$template->budget = $budget;
		$template->points = $points;
		$template->type = $this->type;
		$template->render();
	}

	#region "Helper methods"

	protected function getData() {
		$history = $this->sessionService->getEvaluationHistory($this->trainingStepId);
		$budget = array();
		$points = array();

		$maxTime = 0;

		foreach ($history as $data) {
			if ($data['internalTime'] > $maxTime) {
				$maxTime = $data['internalTime'];
			}
		}

		foreach ($history as $data) {
			if ($data['internalTime'] < $maxTime - $this->historyTimeLimit)
				continue;

			$budget[] = array($data['internalTime'], $data['budget']);
			$points[] = array($data['internalTime'], $data['evaluationPoints']);
		}

		return array($budget, $points);
	}

	#endregion

	#region "Get & set"

	/**
	 * @param mixed $sessionService
	 */
	public function setSessionService($sessionService)
	{
		$this->sessionService = $sessionService;
	}

	/**
	 * @return mixed
	 */
	public function getSessionService()
	{
		return $this->sessionService;
	}

	/**
	 * @param int $trainingStepId
	 */
	public function setTrainingStepId($trainingStepId)
	{
		$this->trainingStepId = $trainingStepId;
	}

	/**
	 * @return int
	 */
	public function getTrainingStepId()
	{
		return $this->trainingStepId;
	}

	/**
	 * @param int $historyTimeLimit
	 */
	public function setHistoryTimeLimit($historyTimeLimit)
	{
		$this->historyTimeLimit = $historyTimeLimit;
	}

	/**
	 * @return int
	 */
	public function getHistoryTimeLimit()
	{
		return $this->historyTimeLimit;
	}

	#endregion
}