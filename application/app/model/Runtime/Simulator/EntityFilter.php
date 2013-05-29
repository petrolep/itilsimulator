<?php
/**
 * OperationalArtifactFilter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 26.4.13 21:25
 */

namespace ITILSimulator\Runtime\Simulator;


use Doctrine\ORM\QueryBuilder;

/**
 * Entity filter used in table list controls
 * @package ITILSimulator\Runtime\Simulator
 */
class EntityFilter
{
	/** @var array WHERE conditions */
	protected $where = array();

	/** @var array LIKE conditions */
	protected $like = array();

	/**
	 * Add new LIKE condition
	 * @param string $column Column name
	 * @param string $value Value
	 */
	public function addLike($column, $value) {
		$this->like[] = array($column, $value);
	}

	/**
	 * Add new WHERE condition
	 * @param string $column Column name
	 * @param string $value Value
	 */
	public function addWhere($column, $value) {
		$this->where[] = array($column, $value);
	}

	/**
	 * Apply condition to given query
	 * @param QueryBuilder $query
	 * @param $prefix Target entity prefix used in DQL query
	 */
	public function apply(QueryBuilder $query, $prefix) {
		foreach($this->where as $key => $where) {
			$query->andWhere(sprintf('%s.%s = :where_%s', $prefix, $where[0], $key));
			$query->setParameter('where_' . $key, $where[1]);
		}

		foreach($this->like as $key => $like) {
			$query->andWhere(sprintf('%s.%s LIKE :like_%s', $prefix, $like[0], $key));
			$query->setParameter('like_' . $key, '%' . $like[1] . '%');
		}
	}
}