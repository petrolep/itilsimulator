<?php
/**
 * BaseRepository.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 6.4.13 17:28
 */

namespace ITILSimulator\Repositories;

use Doctrine\ORM\EntityRepository;

/**
 * Base repository to be inherited by specified entities.
 * @package ITILSimulator\Repositories
 */
abstract class BaseRepository extends EntityRepository {
	public function save($object) {
		$this->getEntityManager()->persist($object);
	}

	public function commit() {
		$this->getEntityManager()->flush();
	}

	public function remove($object) {
		$this->getEntityManager()->remove($object);
	}
}