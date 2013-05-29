<?php
/**
 * OperationEventRepository.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 21:18
 */

namespace ITILSimulator\Repositories\OperationArtifact;

use ITILSimulator\Entities\OperationArtifact\OperationEvent;
use ITILSimulator\Entities\Session\ScenarioStep;

/**
 * Operation events repository.
 * @package ITILSimulator\Repositories\OperationArtifact
 */
class OperationEventRepository extends OperationArtifactRepository
{
	public function undo(ScenarioStep $scenarioStep) {
		$this->doUndo($scenarioStep, 'ITILSimulator\Entities\OperationArtifact\OperationEvent');
	}
}