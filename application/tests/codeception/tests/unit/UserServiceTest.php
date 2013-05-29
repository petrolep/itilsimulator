<?php
/**
 * UserServiceTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 21.5.13 11:18
 */
class UserServiceTest extends \Codeception\TestCase\Test
{
	protected $userEntityClass = 'ITILSimulator\Entities\Simulator\User';
	/** @var \ITILSimulator\Services\UserService */
	protected $service;

	/** @var \ITILSimulator\Base\ITILConfigurator */
	protected $config;

	public function setUp() {
		parent::setUp();

		$em = \Codeception\Module\Doctrine2::$em;

		$factory = new \ITILSimulator\Base\DoctrineFactory();
		$this->config = new \ITILSimulator\Base\ITILConfigurator(array());

		$service = new \ITILSimulator\Services\UserService();
		$service->setUserRepository($factory->createUserRepository($em));
		$service->setConfigurator($this->config);

		$this->service = $service;
	}

	public function test() {
		// login as default user
		$this->codeGuy->seeInRepository($this->userEntityClass, array('email' => 'admin@example.com'));
		$identity = $this->service->createUserIdentity('admin@example.com', 'admin@example.com');
		$this->assertNotNull($identity);

		$count = count($this->service->getUsers());

		// create new user
		/** @var \ITILSimulator\Entities\Simulator\User $user */
		$user = new $this->userEntityClass;
		$user->setName('Generated user');
		$user->setEmail($email = 'generated@example.com');
		$user->setPassword('pass', $this->config->getHashMethod());

		$this->service->updateUser($user);

		$this->codeGuy->seeInRepository($this->userEntityClass, array('email' => $email));
		$this->assertEquals($count + 1, count($this->service->getUsers()));

		// login as new user
		$identity = $this->service->createUserIdentity('generated@example.com', 'pass');
		$this->codeGuy->flushToDatabase();
		$this->assertNotNull($identity);
		$this->assertEquals($user->getName(), $identity->getId()->getName());

		// update user
		$user->setName('Updated user name');
		$this->service->updateUser($user);
		$this->codeGuy->flushToDatabase();

		$savedUser = $this->service->getUserByEmail($user->getEmail());
		$this->assertEquals($user->getName(), $savedUser->getName());

		// unique name
		$this->assertFalse($this->service->isEmailUnique($user->getEmail(), 0));
		$this->assertTrue($this->service->isEmailUnique($user->getEmail(), $user->getId()));

		// delete user
		$this->service->deleteUser($user);
		$this->codeGuy->dontSeeInRepository($this->userEntityClass, array('email' => $email));
		$this->assertEquals($count, count($this->service->getUsers()));
	}
}