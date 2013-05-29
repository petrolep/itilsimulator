<?php
/**
 * TrainingStepRepository.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 10.4.13 22:57
 */

namespace ITILSimulator\Repositories\Session;


use Doctrine\DBAL\Portability\Connection;
use Doctrine\ORM\Query;
use ITILSimulator\Repositories\BaseRepository;
use Nette\Diagnostics\Debugger;

/**
 * Training step repository.
 * @package ITILSimulator\Repositories\Session
 */
class TrainingStepRepository extends BaseRepository
{
	/**
	 * Load history of evaluations for selected training.
	 * @param $trainingStepId
	 * @return mixed
	 */
	public function getEvaluationHistory($trainingStepId) {
		$query = $this->getEntityManager()->createQueryBuilder();
		$query->select('scenario.id, scenario.internalTime, scenario.evaluationPoints, scenario.budget')
			->from('ITILSimulator\Entities\Session\ScenarioStep', 'scenario')
			->where('scenario.trainingStep = ?1')
			->andWhere('scenario.isUndid = 0')
			->orderBy('scenario.id');

		return $query->getQuery()->execute(array(1 => $trainingStepId), Query::HYDRATE_ARRAY);
	}

	/**
	 * Return active service specifications for selected training step and service.
	 * (There can be multiple specifications of the same service in the database. Select the most recent and not undid.)
	 * @param $trainingStepId
	 * @param $serviceId
	 * @return int
	 */
	public function getActiveServiceSpecificationId($trainingStepId, $serviceId) {
		$id = $this->getEntityManager()->getConnection()
			->executeQuery('
			SELECT MAX(service_specification_id) service_specification_id
			FROM (
				SELECT ss.id scenario_step_id, sp.id service_specification_id, sp.service_id
				FROM scenario_steps ss
				INNER JOIN service_specifications_per_scenario_steps sspss ON sspss.scenario_step_id = ss.id
				INNER JOIN service_specifications sp ON sp.id = sspss.service_specification_id
				WHERE ss.isUndid = 0 AND ss.trainingStep_id = ? AND sp.service_id = ?
				ORDER BY ss.date DESC
			) x
			GROUP BY x.service_id
			', array($trainingStepId, $serviceId))
			->fetch();

		return $id['service_specification_id'];
	}

	/**
	 * Return active configuration items specifications for selected training step and CI.
	 * (There can be multiple specifications of the same CI in the database. Select the most recent and not undid.)
	 * @param $trainingStepId
	 * @param $configurationItemId
	 * @return mixed
	 */
	public function getActiveConfigurationItemSpecificationId($trainingStepId, $configurationItemId) {
		/*
		$query = $this->getEntityManager()->createQueryBuilder()
			->from('ITILSimulator\Entities\Training\ConfigurationItemSpecification', 'spec')
			->innerJoin('spec.configurationItem', 'ci')
			->innerJoin('spec.scenarioStep', 'step')
			->select('ci.id configuration_item_id, MAX(spec.id) configuration_item_specification_id')
			->where('step.isUndid = 0')
			->andWhere('step.trainingStep = ?1')
			->andWhere('ci IN (?2)')
			->groupBy('ci');
		*/
		$id = $this->getEntityManager()->getConnection()
			->executeQuery('
			SELECT MAX(configuration_item_specification_id) configuration_item_specification_id
			FROM (
				SELECT ss.id scenario_step_id, cis.id configuration_item_specification_id, cis.configurationItem_id
				FROM scenario_steps ss
				INNER JOIN configuration_item_specifications_per_scenario_steps cispss ON cispss.scenario_step_id = ss.id
				INNER JOIN configuration_item_specifications cis ON cis.id = cispss.configuration_item_specification_id
				WHERE ss.isUndid = 0 AND ss.trainingStep_id = ? AND cis.configurationItem_id = ?
				ORDER BY ss.date DESC
			) x
			GROUP BY x.configurationItem_id
			', array($trainingStepId, $configurationItemId))
			->fetch();

		return $id['configuration_item_specification_id'];
	}

	/**
	 * Return active workflow activity specifications for selected training step and workflow activity.
	 * (There can be multiple specifications of the same activity in the database. Select the most recent and not undid.)
	 * @param $trainingStepId
	 * @param $workflowActivityId
	 * @return mixed
	 */
	public function getActiveWorkflowActivitySpecificationId($trainingStepId, $workflowActivityId) {
		$id = $this->getEntityManager()->getConnection()
			->executeQuery('
			SELECT MAX(workflow_activity_specification_id) workflow_activity_specification_id
			FROM (
				SELECT ss.id scenario_step_id, was.id workflow_activity_specification_id, was.workflowActivity_id
				FROM scenario_steps ss
				INNER JOIN workflow_activity_specifications_per_scenario_steps waspss ON waspss.scenario_step_id = ss.id
				INNER JOIN workflow_activity_specifications was ON was.id = waspss.workflow_activity_specification_id
				WHERE ss.isUndid = 0 AND ss.trainingStep_id = ? AND was.workflowActivity_id = ?
				ORDER BY ss.date DESC
			) x
			GROUP BY x.workflowActivity_id
			', array($trainingStepId, $workflowActivityId))
			->fetch();

		return $id['workflow_activity_specification_id'];
	}
}