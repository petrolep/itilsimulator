<?php
/**
 * SessionRepository.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 10.4.13 22:49
 */

namespace ITILSimulator\Repositories\Session;


use ITILSimulator\Repositories\BaseRepository;

/**
 * Session repository
 * @package ITILSimulator\Repositories\Session
 */
class SessionRepository extends BaseRepository
{
	/**
	 * Return available sessions of selected user.
	 * @param $userId
	 * @return array
	 */
	public function getSessionsByUser($userId) {
		return $this->findBy(array('user' => $userId), array('dateEnd' => 'DESC'));
	}
}