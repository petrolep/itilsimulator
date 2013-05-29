<?php
/**
 * Role.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 27.4.13 17:56
 */

namespace ITILSimulator\Entities\Simulator;


use Nette\Object;

/**
 * Access role (Doctrine entity).
 * @Entity(repositoryClass="ITILSimulator\Repositories\Simulator\RoleRepository")
 * @Table(name="roles")
 **/
class Role extends Object
{
	#region "Properties"

	/**
	 * @Id
	 * @Column(type="integer") @GeneratedValue
	 * @var int
	 **/
	protected $id;

	/**
	 * @Column(type="string", length=50, unique=true)
	 * @var string
	 **/
	protected $code;

	/**
	 * @Column(type="string", length=50)
	 * @var string
	 **/
	protected $name;

	/**
	 * @ManyToMany(targetEntity="ITILSimulator\Entities\Simulator\User", mappedBy="roles")
	 * @var User[]
	 */
	protected $users;

	#endregion

	#region "Get & set"

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param User[] $users
	 */
	public function setUsers($users)
	{
		$this->users = $users;
	}

	/**
	 * @return User[]
	 */
	public function getUsers()
	{
		return $this->users;
	}

	#endregion


}