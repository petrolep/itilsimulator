<?php
/**
 * OperationIncident.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 21:16
 */

namespace ITILSimulator\Entities\OperationArtifact;

/**
 * Operation incident class (Doctrine entity). Inherits OperationArtifact.
 * @Entity(repositoryClass="ITILSimulator\Repositories\OperationArtifact\OperationIncidentRepository")
 * @Table(name="operation_incidents")
 **/
class OperationIncident extends OperationArtifact
{
	const STATUS_NEW = 0;
	const STATUS_ACCEPTED = 1;
	const STATUS_ASSIGNED = 2;
	const STATUS_IN_PROGRESS = 3;
	const STATUS_SOLVED = 4;
	const STATUS_CLOSED = 5;

	#region "Properties"

	/**
	 * @Column(type="string", length=20)
	 * @var string
	 */
	protected $referenceNumber;

	/**
	 * @Column(type="integer")
	 * @var int
	 */
	protected $level = 1;

	/**
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $canBeEscalated = true;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $history;

	/**
	 * @Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $priority;

	/**
	 * @Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $urgency;

	/**
	 * @Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $impact;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $symptoms;

	/**
	 * @Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $timeToResponse;

	/**
	 * @Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $timeToResolve;

	/**
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $isMajor = false;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\OperationArtifact\OperationCategory", inversedBy="operationalIncidents")
	 * @JoinColumn(onDelete="SET NULL")
	 * @var OperationCategory
	 */
	protected $category;

	#endregion

	public function __construct()
	{
		$this->referenceNumber = 'I-' . \Nette\Utils\Strings::random(6, 'A-Z0-9');
	}

	/**
	 * Log operation which was done with the incident
	 * @param $text
	 */
	public function logHistory($text)
	{
		$this->history = trim($text . "\n" . $this->history);
	}

	/**
	 * Mark incident as assigned
	 */
	public function assign()
	{
		parent::assign();

		$this->setTimeToResponse(0);
	}

	#region "Override"

	/**
	 * Change incident's status. If the status is SOLVED or CLOSED, value timeToResolve is cleared.
	 * @param int $status
	 */
	public function setStatus($status)
	{
		parent::setStatus($status);

		if ($status == OperationIncident::STATUS_SOLVED || $status == OperationIncident::STATUS_CLOSED) {
			// incident has been resolved
			$this->setTimeToResolve(0);
		}
	}

	#endregion

	#region "Get & set"

	/**
	 * @param string $referenceNumber
	 */
	public function setReferenceNumber($referenceNumber)
	{
		$this->referenceNumber = $referenceNumber;
	}

	/**
	 * @return string
	 */
	public function getReferenceNumber()
	{
		return $this->referenceNumber;
	}

	/**
	 * @param boolean $canBeEscalated
	 */
	public function setCanBeEscalated($canBeEscalated)
	{
		$this->canBeEscalated = $canBeEscalated;
	}

	/**
	 * @return boolean
	 */
	public function getCanBeEscalated()
	{
		return $this->canBeEscalated;
	}

	/**
	 * @param int $level
	 */
	public function setLevel($level)
	{
		$this->level = $level;
	}

	/**
	 * @return int
	 */
	public function getLevel()
	{
		return $this->level;
	}

	/**
	 * Set history of operations done with the incident
	 * @param string $history
	 */
	public function setHistory($history)
	{
		$this->history = $history;
	}

	/**
	 * History of operations done with the incident
	 * @return string
	 */
	public function getHistory()
	{
		return $this->history;
	}

	/**
	 * @param int $impact
	 */
	public function setImpact($impact)
	{
		$this->impact = $impact;
	}

	/**
	 * @return int
	 */
	public function getImpact()
	{
		return $this->impact;
	}

	/**
	 * @param boolean $isMajor
	 */
	public function setIsMajor($isMajor)
	{
		$this->isMajor = $isMajor;
	}

	/**
	 * @return boolean
	 */
	public function getIsMajor()
	{
		return $this->isMajor;
	}

	/**
	 * @param int $priority
	 */
	public function setPriority($priority)
	{
		$this->priority = $priority;
	}

	/**
	 * @return int
	 */
	public function getPriority()
	{
		return $this->priority;
	}

	/**
	 * @param string $symptoms
	 */
	public function setSymptoms($symptoms)
	{
		$this->symptoms = $symptoms;
	}

	/**
	 * @return string
	 */
	public function getSymptoms()
	{
		return $this->symptoms;
	}

	/**
	 * @param int $timeToResolve
	 */
	public function setTimeToResolve($timeToResolve)
	{
		$this->timeToResolve = $timeToResolve;
	}

	/**
	 * @return int
	 */
	public function getTimeToResolve()
	{
		return $this->timeToResolve;
	}

	/**
	 * @param int $timeToResponse
	 */
	public function setTimeToResponse($timeToResponse)
	{
		$this->timeToResponse = $timeToResponse;
	}

	/**
	 * @return int
	 */
	public function getTimeToResponse()
	{
		return $this->timeToResponse;
	}

	/**
	 * @param int $urgency
	 */
	public function setUrgency($urgency)
	{
		$this->urgency = $urgency;
	}

	/**
	 * @return int
	 */
	public function getUrgency()
	{
		return $this->urgency;
	}

	/**
	 * @param \ITILSimulator\Entities\OperationArtifact\OperationCategory $category
	 */
	public function setCategory($category)
	{
		$this->category = $category;
	}

	/**
	 * @return \ITILSimulator\Entities\OperationArtifact\OperationCategory
	 */
	public function getCategory()
	{
		return $this->category;
	}

	#endregion
}