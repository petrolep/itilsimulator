<?php
/**
 * OperationArtifactRepository.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 16.4.13 21:51
 */

namespace ITILSimulator\Repositories\OperationArtifact;


use ITILSimulator\Entities\OperationArtifact\OperationArtifact;
use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Repositories\BaseRepository;
use ITILSimulator\Runtime\OperationArtifact\OperationArtifactPersistentUnit;
use ITILSimulator\Runtime\Simulator\EntityFilter;
use Nette\Diagnostics\Debugger;
use Nette\InvalidStateException;

/**
 * Operation artifact (event, incident, problem) repository.
 * @package ITILSimulator\Repositories\OperationArtifact
 */
abstract class OperationArtifactRepository extends BaseRepository
{
	private $objectsCache = array();

	/**
	 * Save operation artifact. If the artifact has been updated, invalidate old record and create a new one.
	 * If the artifact is new insert it as a new record.
	 * @param $artifactUnit
	 * @return OperationArtifact|void
	 * @throws \Nette\InvalidStateException
	 */
	public function save($artifactUnit) {
		if (!$artifactUnit instanceof OperationArtifactPersistentUnit)
			throw new InvalidStateException('Entities must be wrapped in OperationArtifactPersistentUnit.');

		$validTil = $artifactUnit->getScenarioStep();

		if (!$artifactUnit->getOperationArtifact()->getId()) {
			// new artifact
			return $this->create($artifactUnit, $validTil);

		} else {
			// updated artifact
			return $this->update($artifactUnit, $validTil);
		}
	}

	/**
	 * Update existing artifact. Invalidate current version of the artifact and create a new one.
	 * @param OperationArtifactPersistentUnit $artifactUnit
	 * @param $validFrom
	 * @return OperationArtifact
	 */
	protected function update(OperationArtifactPersistentUnit $artifactUnit, $validFrom) {
		// clone artifact to insert as a new record
		$originalArtifact = $artifactUnit->getOperationArtifact();
		if ($originalArtifact->getId()) {
			// update only objects already persisted in DB

			$modifiedArtifact = clone $artifactUnit->getOperationArtifact();
			//$modifiedArtifact->setTrainingStep($artifactUnit->getScenarioStep()->getTrainingStep());

			$modifiedArtifact->setOriginalId($originalArtifact->getOriginalId() ?: $originalArtifact->getId());
			$modifiedArtifact->validate($validFrom);
			$this->getEntityManager()->persist($modifiedArtifact);

			// ensure original entity was not modified and invalidate its validity
			$this->getEntityManager()->refresh($originalArtifact);
			$originalArtifact->invalidate($validFrom);
			$this->getEntityManager()->persist($originalArtifact);

		} else {
			// new objects (not yet in the DB) simply return back
			$modifiedArtifact = $originalArtifact;
		}

		$this->objectsCache[$modifiedArtifact->getOriginalId()] = $modifiedArtifact;

		return $modifiedArtifact;
	}

	/**
	 * Persist new operation artifact.
	 * @param OperationArtifactPersistentUnit $artifactUnit
	 * @param $validFrom
	 * @return OperationArtifact
	 */
	protected function create(OperationArtifactPersistentUnit $artifactUnit, $validFrom) {
		// insert a new record
		$artifact = $artifactUnit->getOperationArtifact();
		//$artifact->setTrainingStep($artifactUnit->getScenarioStep()->getTrainingStep());
		$artifact->validate($validFrom);
		$artifact->setDate(new \DateTime());
		$this->getEntityManager()->persist($artifact);

		return $artifact;
	}

	/**
	 * Undo operation artifacts up to selected scenario step.
	 * @param ScenarioStep $scenarioStep
	 * @param $className
	 */
	protected function doUndo(ScenarioStep $scenarioStep, $className) {
		// mark as undid all artifacts created "in the future" (after the scenario step we are returning to)
		$dqlUndid = 'UPDATE ' . $className . ' e SET e.isUndid = 1, e.scenarioStepTo = ?1 WHERE e.scenarioStepFrom >= ?2';

		$this->getEntityManager()->createQuery($dqlUndid)
			->setParameter(1, $scenarioStep)
			->setParameter(2, $scenarioStep)
			->execute();

		// reactive all artifacts active in the original scenario step
		$dqlNull = 'UPDATE ' . $className . ' e SET e.scenarioStepTo = NULL WHERE e.isUndid = 0 AND e.scenarioStepTo >= ?1';
		$this->getEntityManager()->createQuery($dqlNull)
			->setParameter(1, $scenarioStep)
			->execute();
	}

	/**
	 * Return available artifacts matching selected class, scenario and where condition.
	 * @param $className
	 * @param $scenarioStepId
	 * @param int $limit
	 * @param int $offset
	 * @param null $where
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function findAvailableArtifactsQuery($className, $scenarioStepId, $limit = 0, $offset = 0, $where = NULL) {
		$trainingId = $this->getEntityManager()->createQueryBuilder()
			->select('t.id')
			->from('ITILSimulator\Entities\Session\ScenarioStep', 's')
			->join('s.trainingStep', 't')
			->where('s.id = ?1')
			->setParameter(1, $scenarioStepId)
			->getQuery()
			->getSingleScalarResult();

		$query = $this->getEntityManager()->createQueryBuilder()
			->select('a')
			->from($className, 'a')
			->join('a.scenarioStepFrom', 's')
			->where('a.scenarioStepFrom <= ?1')
			->andWhere('a.scenarioStepTo IS NULL')
			->andWhere('a.isUndid = 0')
			->andWhere('s.trainingStep = ?2')
			->orderBy('a.date', 'DESC');

		$query->setParameter(1, $scenarioStepId);
		$query->setParameter(2, $trainingId);

		if ($limit)
			$query->setMaxResults($limit);

		if ($offset)
			$query->setFirstResult($offset);

		if ($where instanceof EntityFilter) {
			$where->apply($query, 'a');
		}

		return $query;
	}

	/**
	 * Return available artifacts matching the scenario and filter.
	 * @param $scenarioStepId
	 * @param int $limit
	 * @param int $offset
	 * @param null $filter
	 * @return array
	 */
	public function findAvailable($scenarioStepId, $limit = 0, $offset = 0, $filter = NULL) {
		/** @var OperationArtifact[] $data */
		$data = $this->findAvailableArtifactsQuery($this->_entityName, $scenarioStepId, $limit, $offset, $filter)->getQuery()->execute();
		$result = array();
		foreach ($data as $artifact) {
			if (isset($this->objectsCache[$artifact->getOriginalId() ?: $artifact->getId()])) {
				// we have modified object in memory (newer version available)
				$result[] = $this->objectsCache[$artifact->getOriginalId() ?: $artifact->getId()];

			} else {
				// untouched object from database
				$result[] = $artifact;
			}
		}

		return $result;
	}

	/**
	 * Count available artifacts matching the scenario and filter.
	 * @param $scenarioStepId
	 * @param null $filter
	 * @return mixed
	 */
	public function countAvailable($scenarioStepId, $filter = NULL) {
		return $this->findAvailableArtifactsQuery($this->_entityName, $scenarioStepId, 0, 0, $filter)
			->select('COUNT(a)')
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * Find active version of selected operation artifact.
	 * (There can be multiple versions of the same artifact in the database. However only one of them should be active.)
	 * @param $id
	 * @return mixed|null
	 */
	public function findOneActive($id) {
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('a')
			->from($this->_entityName, 'a')
			->where(
				$qb->expr()->orX(
					$qb->expr()->andX(
						$qb->expr()->eq('a.originalId', '?1'),
						$qb->expr()->isNull('a.scenarioStepTo')
					),
					$qb->expr()->andX(
						$qb->expr()->isNull('a.scenarioStepTo'),
						$qb->expr()->eq('a.id', '?1')
					)
				)
			);

		$qb->setParameter(1, $id);

		if ($result = $qb->getQuery()->execute())
			return reset($result);

		return null;
	}
}