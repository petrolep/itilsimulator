<?php
/**
 * ConfigurationItemSpecificationRepository.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 11:46
 */

namespace ITILSimulator\Repositories\Training;


use Doctrine\ORM\Query;
use ITILSimulator\Repositories\BaseRepository;

/**
 * Configuration item specification repository
 * @package ITILSimulator\Repositories\Training
 */
class ConfigurationItemSpecificationRepository extends BaseRepository
{
	public function getAttributesHistory($configurationItemId, $trainingStepId) {
		$query = $this->getEntityManager()->createQueryBuilder();
		$query
			->select('scenario.internalTime, ci.id, ci.name, spec.attributes')
			->from('ITILSimulator\Entities\Training\ConfigurationItemSpecification', 'spec')
			->join('spec.configurationItem', 'ci')
			->leftjoin('spec.scenarioSteps', 'scenario')
			->leftjoin('scenario.trainingStep', 'training')
			->where('spec.configurationItem = ?1')
			->andWhere(
				$query->expr()->orX(
					$query->expr()->eq('training', $trainingStepId),
					$query->expr()->eq('spec.isDefault', true)
				)
			)
			->andWhere(
				$query->expr()->orX(
					$query->expr()->eq('scenario.isUndid', 0),
					$query->expr()->isNull('scenario.isUndid')
				)
			)
			->orderBy('scenario.internalTime');

		return $query->getQuery()
			->setParameter(1, $configurationItemId)
			->execute(array(), Query::HYDRATE_ARRAY);
	}
}