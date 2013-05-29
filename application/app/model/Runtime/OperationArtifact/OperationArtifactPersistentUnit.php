<?php
/**
 * ActiveOperationArtifact.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 16.4.13 21:44
 */

namespace ITILSimulator\Runtime\OperationArtifact;


use ITILSimulator\Entities\OperationArtifact\OperationArtifact;
use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Entities\Session\TrainingStep;
use Nette\Object;

/**
 * Helper class used for persisting operation artifacts (events, problems, artifacts).
 * Contains information also about ScenarioStep -- to update temporal information.
 * @package ITILSimulator\Runtime\OperationArtifact
 */
class OperationArtifactPersistentUnit extends Object
{
	/** @var OperationArtifact */
	public $operationArtifact;

	/** @var ScenarioStep */
	public $scenarioStep;

	public function __construct(OperationArtifact $operationArtifact, ScenarioStep $scenarioStep) {
		$this->operationArtifact = $operationArtifact;
		$this->scenarioStep = $scenarioStep;
	}

	/**
	 * @return \ITILSimulator\Entities\OperationArtifact\OperationArtifact
	 */
	public function getOperationArtifact()
	{
		return $this->operationArtifact;
	}

	/**
	 * @return \ITILSimulator\Entities\Session\ScenarioStep
	 */
	public function getScenarioStep()
	{
		return $this->scenarioStep;
	}




}