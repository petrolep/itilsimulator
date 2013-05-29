<?php
/**
 * Session.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 10.4.13 22:44
 */

namespace ITILSimulator\Entities\Session;


use Doctrine\Common\Collections\ArrayCollection;
use ITILSimulator\Entities\Simulator\User;
use ITILSimulator\Entities\Training\Training;
use Nette\InvalidStateException;
use Nette\Object;

/**
 * Session class (Doctrine entity). Represents session of a user for one training.
 * @Entity(repositoryClass="ITILSimulator\Repositories\Session\SessionRepository")
 * @Table(name="sessions")
 **/
class Session extends Object
{
	#region "Properties"

	/**
	 * @Id
	 * @Column(type="integer") @GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Simulator\User")
	 * @var User
	 */
	protected $user;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Training\Training")
	 * @JoinColumn(onDelete="CASCADE")
	 * @var Training
	 */
	protected $training;

	/**
	 * @OneToMany(targetEntity="ITILSimulator\Entities\Session\TrainingStep", mappedBy="session", cascade={"remove"})
	 * @var ArrayCollection|TrainingStep[]
	 */
	protected $trainingSteps;

	/**
	 * @Column(type="datetime", name="date_start")
	 * @var \DateTime
	 */
	protected $dateStart;

	/**
	 * @Column(type="datetime", name="date_end")
	 * @var \DateTime
	 */
	protected $dateEnd;

	/**
	 * @Column(type="boolean", name="is_finished")
	 * @var bool
	 */
	protected $isFinished = false;

	#endregion

	public function __construct(User $user, Training $training)
	{
		$this->user = $user;
		$this->dateStart = new \DateTime();
		$this->dateEnd = $this->dateStart;
		$this->training = $training;

		$this->trainingSteps = new ArrayCollection();
	}

	/**
	 * Finish session
	 * @throws \Nette\InvalidStateException
	 */
	public function finish() {
		if ($this->isFinished()) {
			throw new InvalidStateException('Session already finished.');
		}

		$this->setDateEnd(new \DateTime());
		$this->isFinished = true;
	}

	/**
	 * Get user ID of training creator
	 * @return int
	 */
	public function getTrainingCreatorUserId() {
		return $this->getTraining()->getUserId();
	}

	/**
	 * Get user ID of session user (player)
	 * @return int
	 */
	public function getUserId() {
		return $this->user->getId();
	}

	#region "Get & set"

	/**
	 * @param \DateTime $dateEnd
	 */
	public function setDateEnd($dateEnd)
	{
		$this->dateEnd = $dateEnd;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateEnd()
	{
		return $this->dateEnd;
	}

	/**
	 * @param \DateTime $dateStart
	 */
	public function setDateStart($dateStart)
	{
		$this->dateStart = $dateStart;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateStart()
	{
		return $this->dateStart;
	}

	/**
	 * @return boolean
	 */
	public function getIsFinished()
	{
		return $this->isFinished;
	}

	/**
	 * @return boolean
	 */
	public function isFinished()
	{
		return $this->getIsFinished();
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return \ITILSimulator\Entities\Training\Training
	 */
	public function getTraining()
	{
		return $this->training;
	}

	/**
	 * @return ArrayCollection|TrainingStep[]
	 */
	public function getTrainingSteps()
	{
		return $this->trainingSteps;
	}

	/**
	 * @return \ITILSimulator\Entities\Simulator\User
	 */
	public function getUser()
	{
		return $this->user;
	}

	#endregion
}