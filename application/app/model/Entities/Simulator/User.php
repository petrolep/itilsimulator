<?php
/**
 * User.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 6.4.13 16:51
 */

namespace ITILSimulator\Entities\Simulator;

use Doctrine\Common\Collections\ArrayCollection;
use ITILSimulator\Entities\Session\Session;
use ITILSimulator\Entities\Strings;
use Nette\Diagnostics\Debugger;
use Nette\Object;

/**
 * User (Doctrine entity).
 * @Entity(repositoryClass="ITILSimulator\Repositories\Simulator\UserRepository")
 * @Table(name="users")
 **/
class User extends Object
{
	#region "Properties"

	/** @Id
	 * @Column(type="integer") @GeneratedValue
	 * @var int
	 **/
	protected $id;

	/**
	 * @Column(type="string")
	 * @var string
	 **/
	protected $name;

	/**
	 * @Column(type="string", unique=true)
	 * @var string
	 **/
	protected $email;

	/**
	 * @Column(type="string", length=100, nullable=true)
	 * @var string
	 **/
	protected $password;

	/**
	 * @Column(type="string", length=25, nullable=true)
	 * @var string
	 **/
	protected $passwordSalt;

	/**
	 * @Column(type="datetime")
	 * @var \DateTime
	 */
	protected $dateRegistration;

	/**
	 * @Column(type="datetime", nullable=true)
	 * @var \DateTime
	 */
	protected $dateLastLogin;

	/**
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $isAnonymous = false;

	/**
	 * @ManyToMany(targetEntity="ITILSimulator\Entities\Simulator\Role", inversedBy="users")
	 * @JoinTable(name="users_per_roles",
	 *      joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@JoinColumn(name="role_id", referencedColumnName="id")}
	 * )
	 * @var Role[]
	 */
	protected $roles;

	/**
	 * @OneToMany(targetEntity="ITILSimulator\Entities\Session\Session", mappedBy="user", cascade={"remove"})
	 * @var Session[]
	 */
	protected $sessions;

	#endregion

	public function __construct() {
		$this->roles = new ArrayCollection();
		$this->sessions = new ArrayCollection();

		$this->passwordSalt = $this->generateSalt();

		$this->dateRegistration = new \DateTime();
	}

	/**
	 * Hash password using selected hash algorithm
	 * @param string $password Plain-text password to be hashed
	 * @param string $hashType Algorithm to be used
	 * @return string
	 */
	protected function hashPassword($password, $hashType) {
		return hash($hashType, $this->passwordSalt . $password);
	}

	/**
	 * Validate password with valid password
	 * @param string $password Plain-text password
	 * @param string $hashType Algorithm to be used
	 * @return bool
	 */
	public function validatePassword($password, $hashType) {
		return $this->getPassword() == $this->hashPassword($password, $hashType);
	}

	/**
	 * Return array of user's roles (as roleid => rolecode)
	 * @return array
	 */
	public function getRolesList()
	{
		$list = array();
		foreach($this->roles as $role) {
			$list[$role->getId()] = $role->getCode();
		}

		return $list;
	}

	public function clearRoles()
	{
		$this->roles->clear();
	}

	/**
	 * @param Role $role
	 */
	public function addRole(Role $role)
	{
		$this->roles->add($role);
	}

	/**
	 * Generate new random password salt
	 * @return string
	 */
	public function generateSalt()
	{
		return \Nette\Utils\Strings::random(22);
	}

	#region "Get & set"

	/**
	 * Change user's password. Password is hashed using selected hash algorithm.
	 * @param string $password Plain-text password
	 * @param string $hashType Algorithm to be used
	 */
	public function setPassword($password, $hashType)
	{
		$this->passwordSalt = $this->generateSalt();
		$this->password = $this->hashPassword($password, $hashType);
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
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
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param \DateTime $dateLastLogin
	 */
	public function setDateLastLogin($dateLastLogin)
	{
		$this->dateLastLogin = $dateLastLogin;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateLastLogin()
	{
		return $this->dateLastLogin;
	}

	/**
	 * @param \DateTime $dateRegistration
	 */
	public function setDateRegistration($dateRegistration)
	{
		$this->dateRegistration = $dateRegistration;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateRegistration()
	{
		return $this->dateRegistration;
	}

	/**
	 * @param $roles
	 */
	public function setRoles($roles)
	{
		$this->roles = $roles;
	}

	/**
	 * @return ArrayCollection|Role[]
	 */
	public function getRoles()
	{
		return $this->roles;
	}

	/**
	 * @param $sessions
	 */
	public function setSessions($sessions)
	{
		$this->sessions = $sessions;
	}

	/**
	 * @return ArrayCollection|\ITILSimulator\Entities\Session\Session[]
	 */
	public function getSessions()
	{
		return $this->sessions;
	}

	/**
	 * @param boolean $isAnonymous
	 */
	public function setIsAnonymous($isAnonymous)
	{
		$this->isAnonymous = $isAnonymous;
	}

	/**
	 * @return boolean
	 */
	public function getIsAnonymous()
	{
		return $this->isAnonymous;
	}

	/**
	 * @return bool
	 */
	public function isAnonymous()
	{
		return $this->getIsAnonymous();
	}

	#endregion

}