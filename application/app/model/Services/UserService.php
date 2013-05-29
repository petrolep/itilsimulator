<?php
/**
 * UserService.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 6.4.13 17:35
 */

namespace ITILSimulator\Services;


use ITILSimulator\Base\ITILConfigurator;
use ITILSimulator\Entities\Simulator\Role;
use ITILSimulator\Entities\Simulator\User;
use ITILSimulator\Repositories\Simulator\RoleRepository;
use ITILSimulator\Repositories\Simulator\UserRepository;
use Nette\Security\Identity;

/**
 * User service. Handles users and roles.
 * @package ITILSimulator\Services
 */
class UserService implements ITransactionService
{
	#region "Properties"

	/** @var UserRepository */
	protected $userRepository;

	/** @var RoleRepository */
	protected $roleRepository;

	/** @var ITILConfigurator */
	protected $configurator;

	#endregion

	#region "Dependencies"

	/**
	 * @param \ITILSimulator\Base\ITILConfigurator $configurator
	 */
	public function setConfigurator(ITILConfigurator $configurator)
	{
		$this->configurator = $configurator;
	}

	/**
	 * @return \ITILSimulator\Base\ITILConfigurator
	 */
	public function getConfigurator()
	{
		return $this->configurator;
	}

	/**
	 * @param \ITILSimulator\Repositories\Simulator\RoleRepository $roleRepository
	 */
	public function setRoleRepository(RoleRepository $roleRepository)
	{
		$this->roleRepository = $roleRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Simulator\RoleRepository
	 */
	public function getRoleRepository()
	{
		return $this->roleRepository;
	}

	/**
	 * @param \ITILSimulator\Repositories\Simulator\UserRepository $userRepository
	 */
	public function setUserRepository(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Simulator\UserRepository
	 */
	public function getUserRepository()
	{
		return $this->userRepository;
	}

	#endregion

	#region "Public API"

	#region "Users"

	/**
	 * Create user identity from username and password. If the credentials are not correct, NULL is returned.
	 * @param string $username
	 * @param string $password
	 * @return Identity|null
	 */
	public function createUserIdentity($username, $password)
	{
		/** @var $user \ITILSimulator\Entities\Simulator\User */
		$user = $this->userRepository->findOneBy(array('email' => $username));

		if (!$user || !$user->validatePassword($password, $this->configurator->getHashMethod())) {
			return null;
		}

		return $this->getUserIdentity($user);
	}

	/**
	 * Return user identity
	 * @param User $user
	 * @return Identity
	 */
	public function getUserIdentity(User $user)
	{
		return new Identity($user, $user->getRolesList());
	}

	/**
	 * Reload user identity from database (to refresh serialized identity, which was detached from entity manager)
	 * @param User $user
	 * @return User
	 */
	public function refreshUserIdentity(User $user)
	{
		return $this->userRepository->find($user->id);
	}

	/**
	 * Return user
	 * @param int $id
	 * @return User
	 */
	public function getUser($id)
	{
		return $this->userRepository->findOneBy(array('id' => $id));
	}

	/**
	 * Return users by email
	 * @param string $email
	 * @return User
	 */
	public function getUserByEmail($email) {
		$users = $this->userRepository->findBy(array('email' => $email));

		return $users ? $users[0] : NULL;
	}

	/**
	 * Update user
	 * @param User $user
	 */
	public function updateUser(User $user)
	{
		$this->userRepository->save($user);
	}

	/**
	 * Return all users
	 * @return array
	 */
	public function getUsers() {
		return $this->userRepository->findBy(array(), array('name' => 'ASC'));
	}

	/**
	 * Delete user
	 * @param User $user
	 */
	public function deleteUser(User $user)
	{
		$this->userRepository->remove($user);
	}

	/**
	 * Check whether the email is unique
	 * @param string $email
	 * @param int $ignoreUserId Ignore this user ID
	 * @return bool
	 */
	public function isEmailUnique($email, $ignoreUserId)
	{
		$existingUser = $this->getUserByEmail($email);

		return !$existingUser || $existingUser->getId() == $ignoreUserId;
	}

	#endregion

	#region "Roles"

	/**
	 * Return role
	 * @param string $code
	 * @return Role
	 */
	public function getRole($code) {
		return $this->roleRepository->findOneBy(array('code' => $code));
	}

	/**
	 * Return all roles
	 * @return Role[]
	 */
	public function getRoles() {
		return $this->roleRepository->findBy(array(), array('name' => 'ASC'));
	}

	#endregion

	#region "Students"

	/**
	 * Return list of students who attended trainings created by creator
	 * @param User $creator
	 * @return User[]
	 */
	public function getStudentsByCreator(User $creator) {
		return $this->userRepository->findByCreator($creator->getId());
	}

	/**
	 * Return student who attended trainings created by creator
	 * @param $studentId
	 * @param User $creator
	 * @return User
	 */
	public function getStudentByCreator($studentId, User $creator) {
		return $this->userRepository->findOneByCreator($studentId, $creator->getId());
	}

	#endregion

	/**
	 * Commit changes to database
	 */
	public function commitChanges() {
		$this->userRepository->commit();
	}

	#endregion
}