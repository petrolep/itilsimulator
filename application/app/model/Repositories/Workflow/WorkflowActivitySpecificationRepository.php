<?php
/**
 * WorkflowActivityRepository.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 14:00
 */

namespace ITILSimulator\Repositories\Workflow;


use ITILSimulator\Repositories\BaseRepository;
use ITILSimulator\Runtime\Workflow\WorkflowActivityStateEnum;

/**
 * Workflow activity specification repository.
 * @package ITILSimulator\Repositories\Workflow
 */
class WorkflowActivitySpecificationRepository extends BaseRepository
{
	/**
	 * Return path of scenarion in selected session.
	 * @param $sessionId
	 * @param $scenarioId
	 * @return mixed
	 */
	public function findByScenarioPath($sessionId, $scenarioId) {
		$query = $this->getEntityManager()->createQueryBuilder()
			->select('spec, activity')
			->from('ITILSimulator\Entities\Workflow\WorkflowActivitySpecification', 'spec')
			->join('spec.workflowActivity', 'activity')
			->join('spec.scenarioSteps', 'scenario')
			->join('scenario.trainingStep', 'training')
			->join('training.session', 'session')
			->where('training.scenario = ?1')
			->andWhere('scenario.isUndid = 0')
			->andWhere('spec.state = ?2')
			->andWhere('session = ?3')
			->orderBy('spec.id');

		$query->setParameter(1, $scenarioId);
		$query->setParameter(2, WorkflowActivityStateEnum::FINISHED);
		$query->setParameter(3, $sessionId);

		return $query->getQuery()->execute();
	}
}