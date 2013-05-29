<?php
/**
 * KnownIssueRepository.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 7.4.13 21:59
 */

namespace ITILSimulator\Repositories\Training;

use ITILSimulator\Repositories\BaseRepository;
use ITILSimulator\Runtime\Simulator\EntityFilter;

/**
 * Known issue (error) repository.
 * @package ITILSimulator\Repositories\Training
 */
class KnownIssueRepository extends BaseRepository
{
	/**
	 * Get all available known errors for selected scenario and filter.
	 * @param $trainingId
	 * @param null $filter
	 * @return mixed
	 */
	public function getAvailable($trainingId, $filter = NULL) {
		$query = $this->getEntityManager()->createQueryBuilder()
			->select('i')
			->from('ITILSimulator\Entities\Training\KnownIssue', 'i')
			->where('i.training = ?1');

		$query->setParameter(1, $trainingId);

		if ($filter instanceof EntityFilter) {
			$filter->apply($query, 'i');
		}

		return $query->getQuery()->execute();
	}
}