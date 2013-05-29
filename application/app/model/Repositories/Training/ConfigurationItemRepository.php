<?php
/**
 * ConfigurationItemRepository.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 7.4.13 21:59
 */

namespace ITILSimulator\Repositories\Training;

use ITILSimulator\Repositories\BaseRepository;

/**
 * Configuration item repository
 * @package ITILSimulator\Repositories\Training
 */
class ConfigurationItemRepository extends BaseRepository
{
	/**
	 * Find all CI by selected service
	 * @param int $serviceId Service ID
	 * @param bool|int $allowGlobal FALSE to return only configuration items from selected service.
	 *                              TRUE to return all global configuration items.
	 *                              INT (User ID) to return only global configuration items from selected user.
	 * @return mixed
	 */
	public function findAllByService($serviceId, $allowGlobal = false) {
		$builder = $this->getEntityManager()->createQueryBuilder();
		$builder->select('c')
			->from('ITILSimulator\Entities\Training\ConfigurationItem', 'c')
			->join('c.services', 's')
			->where('s.id = ?1');

		$builder->setParameter(1, $serviceId);

		if ($allowGlobal) {
			if (is_int($allowGlobal)) {
				// allow globals only for given user
				$builder->join('s.training', 't')
					->orWhere(
						$builder->expr()->andX(
							$builder->expr()->eq('t.user', $allowGlobal),
							$builder->expr()->eq('c.isGlobal', true)
						)
					);

			} else {
				// allow all globals
				$builder->orWhere('c.isGlobal = 1');
			}
		}

		return $builder->getQuery()->execute();
	}
}