<?php
/**
 * TrainingRepository.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 7.4.13 15:28
 */

namespace ITILSimulator\Repositories\Training;


use ITILSimulator\Repositories\BaseRepository;

/**
 * Training repository.
 * @package ITILSimulator\Repositories\Training
 */
class TrainingRepository extends BaseRepository
{
	/**
	 * Get all trainings started/attended by selected user.
	 * @param $userId
	 * @return mixed
	 */
	public function findAllByUser($userId) {
		$dql = 'SELECT t FROM ITILSimulator\Entities\Training\Training t JOIN t.user u WHERE u.id = ?1';

		return $this->getEntityManager()->createQuery($dql)
			->setParameter(1, $userId)
			->execute();
	}

	/**
	 * Returns all published trainings, which are either public or also private.
	 * @param bool $onlyPublic
	 * @return mixed
	 */
	public function findAllPublished($onlyPublic = false) {
		$dql = 'SELECT t FROM ITILSimulator\Entities\Training\Training t WHERE t.isPublished = 1';

		if ($onlyPublic) {
			$dql .= ' AND t.isPublic = 1';
		}

		return $this->getEntityManager()->createQuery($dql)
			->execute();
	}
}