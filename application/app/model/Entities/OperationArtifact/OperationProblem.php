<?php
/**
 * OperationProblem.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 21:16
 */

namespace ITILSimulator\Entities\OperationArtifact;

/**
 * Operation problem class (Doctrine entity). Inherits OperationArtifact.
 * @Entity(repositoryClass="ITILSimulator\Repositories\OperationArtifact\OperationProblemRepository")
 * @Table(name="operation_problems")
 **/
class OperationProblem extends OperationArtifact
{
	const STATUS_NEW = 0;
	const STATUS_INVESTIGATED = 1;
	const STATUS_RESOLVED = 2;
	const STATUS_CLOSED = 3;

	#region "Properties"

	/**
	 * @Column(type="string", length=20)
	 * @var string
	 */
	protected $referenceNumber;

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
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $symptoms;

	/**
	 * @Column(type="text", length=100, nullable=true)
	 * @var string
	 */
	protected $problemOwner;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\OperationArtifact\OperationCategory", inversedBy="operationalProblems")
	 * @JoinColumn(onDelete="SET NULL")
	 * @var OperationCategory
	 */
	protected $category;

	#endregion

	public function __construct()
	{
		$this->referenceNumber = 'P-' . \Nette\Utils\Strings::random(6, 'A-Z0-9');
	}

	/**
	 * Log operation which was done with the incident
	 * @param $text
	 */
	public function logHistory($text)
	{
		$this->history = trim($text . "\n" . $this->history);
	}

	#region "Get & set"

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
	 * @param string $problemOwner
	 */
	public function setProblemOwner($problemOwner)
	{
		$this->problemOwner = $problemOwner;
	}

	/**
	 * @return string
	 */
	public function getProblemOwner()
	{
		return $this->problemOwner;
	}

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

	#endregion

}