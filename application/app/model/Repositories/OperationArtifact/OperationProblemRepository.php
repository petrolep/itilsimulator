<?php
/**
 * OperationIncidentRepository.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 21:18
 */

namespace ITILSimulator\Repositories\OperationArtifact;

use ITILSimulator\Entities\OperationArtifact\OperationProblem;
use ITILSimulator\Entities\Session\ScenarioStep;

/**
 * Operation problems repository.
 * @package ITILSimulator\Repositories\OperationArtifact
 */
class OperationProblemRepository extends OperationArtifactRepository
{
	public function undo(ScenarioStep $scenarioStep) {
		$this->doUndo($scenarioStep, 'ITILSimulator\Entities\OperationArtifact\OperationProblem');
	}
}