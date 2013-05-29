<?php
/**
 * UserTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 20.5.13 10:53
 */

namespace ITILSimulator\Tests;


use ITILSimulator\Entities\Simulator\Role;
use ITILSimulator\Entities\Simulator\User;

require_once(__DIR__ . '/../../../bootstrap.php');


class UserTest extends ITIL_TestCase {
	protected $fields = array(
		'name' => 'John Smith',
		'email' => 'john@example.com',
		'isAnonymous' => false,
	);

	public function testGetSet() {
		$user = new User();

		$this->runGetSetTest($user, $this->fields);

		$this->assertNotNull($user->getDateRegistration());
		$this->assertNull($user->getDateLastLogin());

		$date = new \DateTime();
		$user->setDateLastLogin($date);
		$this->assertEquals($date, $user->getDateLastLogin());

		// roles
		$this->assertEquals(0, $user->getRoles()->count());
		$role = new Role();
		$role->setCode('test role');
		$user->addRole($role);
		$this->assertEquals(1, $user->getRoles()->count());
		$this->assertSame($role, $user->getRoles()->first());

		$roleList = $user->getRolesList();
		$this->assertEquals($role->getCode(), reset($roleList));

		$user->clearRoles();
		$this->assertEquals(0, $user->getRoles()->count());

		// passwords
		$password = 'abc';
		$user->setPassword($password, 'sha1');
		$this->assertNotEquals($password, $user->getPassword());

		$hashedPassword = $user->getPassword();
		$user->setPassword($password, 'sha1');
		$this->assertNotEquals($hashedPassword, $user->getPassword());

		// anonymous
		$this->assertFalse($user->isAnonymous());
		$this->assertFalse($user->getIsAnonymous());
		$user->setIsAnonymous(true);
		$this->assertTrue($user->isAnonymous());
		$this->assertTrue($user->getIsAnonymous());
	}
}
