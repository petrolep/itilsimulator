<?php
/**
 * CustomCacheProvider.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 1.5.13 21:57
 */

namespace ITILSimulator\Base;

use Doctrine\Common\Cache\CacheProvider;
use Nette\Caching\Cache;

/**
 * Cache provider for Doctrine with usage of Nette Cache interface
 * @package ITILSimulator\Base
 */
class CustomCacheProvider extends CacheProvider
{
	/**
	 * @var Cache
	 */
	protected $cache;

	/**
	 * @var array $data
	 */
	private $data = array();

	public function __construct(Cache $cache)
	{
		$this->cache = $cache;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doFetch($id)
	{
		$data = $this->cache->load($id);
		return $data !== NULL ? $data : false;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doContains($id)
	{
		return $this->cache->load($id) !== NULL;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doSave($id, $data, $lifeTime = 0)
	{
		$this->cache->save($id, $data);

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doDelete($id)
	{
		$this->cache->remove($id);

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doFlush()
	{
		$this->cache->clean();

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doGetStats()
	{
		return null;
	}
}
