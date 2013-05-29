<?php
/**
 * ActiveSession.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 18:24
 */

namespace ITILSimulator\Runtime\Session;


use ITILSimulator\Entities\Session\TrainingStep;
use ITILSimulator\Runtime\Training\ActiveService;
use ITILSimulator\Runtime\Workflow\ActiveWorkflow;
use Nette\Object;

/**
 * Active session object. Contains references for current TrainingStep and collections
 * of active services and active workflows.
 * @package ITILSimulator\Runtime\Session
 */
class ActiveSession extends Object
{
	/** @var TrainingStep */
	protected $trainingStep;

	/** @var ActiveService[] */
	protected $activeServices = array();

	/** @var ActiveWorkflow[] */
	protected $activeWorkflows = array();

	public function __construct(TrainingStep $trainingStep, $activeServices, $activeWorkflows) {
		$this->trainingStep = $trainingStep;
		$this->activeServices = $activeServices;
		$this->activeWorkflows = $activeWorkflows;
	}

	/**
	 * @return array|\ITILSimulator\Runtime\Training\ActiveService[]
	 */
	public function getActiveServices()
	{
		return $this->activeServices;
	}

	public function getActiveService($code)
	{
		foreach ($this->activeServices as $activeService) {
			if ($activeService->getServiceCode() == $code)
				return $activeService;
		}

		return null;
	}

	public function getScenario() {
		return $this->trainingStep->getScenario();
	}

	/**
	 * @return array|\ITILSimulator\Runtime\Workflow\ActiveWorkflow[]
	 */
	public function getActiveWorkflows()
	{
		return $this->activeWorkflows;
	}

	/**
	 * @return TrainingStep
	 */
	public function getTrainingStep()
	{
		return $this->trainingStep;
	}

	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection|\ITILSimulator\Entities\Session\ScenarioStep[]
	 */
	public function getScenarioSteps()
	{
		return $this->trainingStep->getScenarioSteps();
	}

	public function getSessionId() {
		return $this->trainingStep->getSessionId();
	}

}