<?php
/**
 * OperationArtifact.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 20:59
 */

namespace ITILSimulator\Entities\OperationArtifact;


use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Entities\Session\TrainingStep;
use Nette\InvalidStateException;
use Nette\Object;
use Symfony\Component\Yaml\Exception\RuntimeException;

/**
 * Operation artifact (Event, Incident, Problem) parent class (Doctrine entity).
 * @MappedSuperclass
 * @HasLifecycleCallbacks
 */
abstract class OperationArtifact extends Object
{
	#region "Properties"

	/**
	 * @Id
	 * @Column(type="integer") @GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $originalId;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Session\ScenarioStep")
	 * @JoinColumn(onDelete="CASCADE")
	 * @var ScenarioStep
	 */
	protected $scenarioStepFrom;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Session\ScenarioStep")
	 * @JoinColumn(onDelete="CASCADE")
	 * @var ScenarioStep
	 */
	protected $scenarioStepTo;

	/**
	 * @Column(type="datetime")
	 * @var \DateTime
	 */
	protected $date;

	/**
	 * @Column(type="integer")
	 * @var int
	 */
	protected $status = 0;

	/**
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $isUndid = false;

	#endregion

	public function __clone() {
		// clear ID
		$this->id = 0;
	}

	/**
	 * Validate artifact (set its validity from selected scenario step)
	 * @param ScenarioStep $scenarioStepFrom
	 */
	public function validate(ScenarioStep $scenarioStepFrom) {
		$this->setScenarioStepFrom($scenarioStepFrom);
		$this->setScenarioStepTo(null);
	}

	/**
	 * Invalidate artifact (set its validity to selected scenarios step)
	 * @param ScenarioStep $scenarioStepTo
	 */
	public function invalidate(ScenarioStep $scenarioStepTo) {
		if (!$this->scenarioStepFrom)
			$this->setScenarioStepFrom($scenarioStepTo);

		$this->setScenarioStepTo($scenarioStepTo);
	}

	/**
	 * Mark artifact as assigned
	 */
	public function assign()
	{
		$this->setStatus(OperationIncident::STATUS_ASSIGNED);
	}

	/**
	 * Return id of original artifact
	 * @return int
	 */
	public function getOriginalId()
	{
		return $this->originalId;
	}

	/**
	 * Set id of original artifact
	 * @param int $originalId
	 */
	public function setOriginalId($originalId)
	{
		$this->originalId = $originalId;
	}

	/**
	 * @return int
	 */
	public function getTrainingStepId()
	{
		return $this->getScenarioStepFrom()->getTrainingStepId();
	}

	/** @PrePersist */
	protected function doOnPrePersist()
	{
		if(!$this->scenarioStepFrom) {
			throw new InvalidStateException('Missing scenario step in OperationArtifact!');
		}
	}

	#region "Get & set"

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Whether the artifact was undid
	 * @return boolean
	 */
	public function getIsUndid()
	{
		return $this->isUndid;
	}

	/**
	 * Set validity from
	 * @param \ITILSimulator\Entities\Session\ScenarioStep $scenarioStepFrom
	 */
	protected function setScenarioStepFrom($scenarioStepFrom)
	{
		$this->scenarioStepFrom = $scenarioStepFrom;
	}

	/**
	 * Return validity from
	 * @return \ITILSimulator\Entities\Session\ScenarioStep
	 */
	public function getScenarioStepFrom()
	{
		return $this->scenarioStepFrom;
	}

	/**
	 * Set validity to
	 * @param \ITILSimulator\Entities\Session\ScenarioStep $scenarioStepTo
	 */
	protected function setScenarioStepTo($scenarioStepTo)
	{
		$this->scenarioStepTo = $scenarioStepTo;
	}

	/**
	 * Return validity to
	 * @return \ITILSimulator\Entities\Session\ScenarioStep
	 */
	public function getScenarioStepTo()
	{
		return $this->scenarioStepTo;
	}

	/**
	 * @param int $status
	 */
	public function setStatus($status)
	{
		$this->status = $status;
	}

	/**
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param \DateTime $date
	 */
	public function setDate($date)
	{
		$this->date = $date;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}

	#endregion
}