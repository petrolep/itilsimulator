<?php
/**
 * DesignService.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 5.5.13 22:07
 */

namespace ITILSimulator\Services;


use ITILSimulator\Base\ITILConfigurator;
use ITILSimulator\Entities\Session\TrainingStep;
use ITILSimulator\Entities\Training\ScenarioDesignResult;
use ITILSimulator\Repositories\Training\ScenarioDesignResultRepository;

/**
 * Design service. Handles service design scenario
 * @package ITILSimulator\Services
 */
class DesignService implements ITransactionService
{
	#region "Properties"

	/** @var ScenarioDesignResultRepository */
	protected $designResultRepository;

	/** @var \ITILSimulator\Base\ITILConfigurator */
	protected $itilConfigurator;

	#endregion

	#region "Dependencies"

	/**
	 * @param \ITILSimulator\Repositories\Training\ScenarioDesignResultRepository $designResultRepository
	 */
	public function setDesignResultRepository(ScenarioDesignResultRepository $designResultRepository)
	{
		$this->designResultRepository = $designResultRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Training\ScenarioDesignResultRepository
	 */
	public function getDesignResultRepository()
	{
		return $this->designResultRepository;
	}

	/**
	 * @param \ITILSimulator\Base\ITILConfigurator $itilConfigurator
	 */
	public function setConfiguration(ITILConfigurator $itilConfigurator)
	{
		$this->itilConfigurator = $itilConfigurator;
	}

	/**
	 * @return \ITILSimulator\Base\ITILConfigurator
	 */
	public function getConfiguration()
	{
		return $this->itilConfigurator;
	}

	#endregion

	#region "Public API"

	/**
	 * Finish design. Creates result record and finishes the training step
	 * @param ScenarioDesignResult $result
	 * @param TrainingStep $trainingStep
	 */
	public function finishDesign(ScenarioDesignResult $result, TrainingStep $trainingStep) {
		$result->setTrainingStep($trainingStep);
		$trainingStep->setBudget(-$result->getPurchaseCost());
		$trainingStep->setEvaluationPoints($this->itilConfigurator->getDesignScenarioPoints());

		$this->designResultRepository->save($result);
		$result->getTrainingStep()->finish();
	}

	/**
	 * Commit changes to database
	 */
	function commitChanges()
	{
		$this->designResultRepository->commit();
	}

	#endregion
}