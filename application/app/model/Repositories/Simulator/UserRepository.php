<?php
/**
 * UserRepository.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 6.4.13 17:25
 */

namespace ITILSimulator\Repositories\Simulator;

use ITILSimulator\Repositories\BaseRepository;

/**
 * User repository
 * @package ITILSimulator\Repositories\Simulator
 */
class UserRepository extends BaseRepository
{
	/**
	 * Find students who attended training created by selected creator.
	 * @param $creatorId
	 * @return mixed
	 */
	public function findByCreator($creatorId) {
		$query = $this->getEntityManager()->createQueryBuilder()
			->select('u')
			->from('ITILSimulator\Entities\Simulator\User', 'u')
			->join('u.sessions', 's')
			->join('s.training', 't')
			->where('t.user = ?1')
			->orderBy('s.id', 'DESC');

		$query->setParameter(1, $creatorId);

		return $query->getQuery()->execute();
	}

	/**
	 * Find detail of selected user while checking if the user attended training created by selected creator.
	 * @param $userId
	 * @param $creatorId
	 * @return mixed
	 */
	public function findOneByCreator($userId, $creatorId) {
		$query = $this->getEntityManager()->createQueryBuilder()
			->select('u')
			->from('ITILSimulator\Entities\Simulator\User', 'u')
			->join('u.sessions', 's')
			->join('s.training', 't')
			->where('t.user = ?1')
			->andWhere('u = ?2');

		$query->setParameter(1, $creatorId);
		$query->setParameter(2, $userId);

		return $query->getQuery()->getSingleResult();
	}
}