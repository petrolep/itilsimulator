<?php
/**
 * IncidentActivity.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 16.4.13 18:45
 */

namespace ITILSimulator\Entities\Workflow\Activities;


use ITILSimulator\Entities\OperationArtifact\OperationCategory;
use ITILSimulator\Entities\OperationArtifact\OperationIncident;
use ITILSimulator\Entities\Workflow\WorkflowActivity;
use ITILSimulator\Runtime\Events\EventTypeEnum;
use ITILSimulator\Runtime\Events\OperationIncidentEvent;
use ITILSimulator\Runtime\ITILEnvironment;
use ITILSimulator\Runtime\RuntimeContext\WorkflowActivityRuntimeContext;

/**
 * Incident activity (Doctrine entity). Generates incidents. Inherits WorkflowActivity.
 * @Entity
 */
class IncidentActivity extends WorkflowActivity
{
	#region "Properties"

	/**
	 * @Column(type="string", length=20, nullable=true)
	 * @var string
	 */
	protected $referenceNumber;

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
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\OperationArtifact\OperationCategory")
	 * @JoinColumn(onDelete="SET NULL")
	 * @var OperationCategory
	 */
	protected $category;

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
	 * @Column(type="integer")
	 * @var int
	 */
	protected $serviceDeskLevel = 1;

	/**
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $canBeEscalated = false;

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
	 * @param int $serviceDeskLevel
	 */
	public function setServiceDeskLevel($serviceDeskLevel)
	{
		$this->serviceDeskLevel = $serviceDeskLevel;
	}

	/**
	 * @return int
	 */
	public function getServiceDeskLevel()
	{
		return $this->serviceDeskLevel;
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

	/**
	 * Executed when the workflow activity starts. Fires ACTIVITY_INCIDENT_CREATED event.
	 * @param WorkflowActivityRuntimeContext $context
	 * @return mixed|null|void
	 */
	public function onStart(WorkflowActivityRuntimeContext $context) {
		$incident = new OperationIncident();
		if($this->referenceNumber)
			$incident->setReferenceNumber($this->referenceNumber);

		$incident->setCanBeEscalated($this->canBeEscalated);
		$incident->setLevel($this->serviceDeskLevel);
		$incident->setImpact($this->impact);
		$incident->setPriority($this->priority);
		$incident->setUrgency($this->urgency);
		$incident->setIsMajor($this->isMajor);
		$incident->setSymptoms($this->symptoms);
		$incident->setCategory($this->getCategory());

		// set time limits for reaction or resolution
		if ($this->timeToResolve)
			$incident->setTimeToResolve(ITILEnvironment::getInstance()->getInternalTime() + $this->timeToResolve);
		if ($this->timeToResponse)
			$incident->setTimeToResponse(ITILEnvironment::getInstance()->getInternalTime() + $this->timeToResponse);

		$event = new OperationIncidentEvent();
		$event->setOperationIncident($incident);
		$event->setDescription($incident->getSymptoms());

		$context->getEventManager()->dispatch(EventTypeEnum::ACTIVITY_INCIDENT_CREATED, $event);

		if (!$this->onEvent) {
			// no condition on the flow, so just finish it
			$context->execute(WorkflowActivityRuntimeContext::FINISH_COMMAND, null);
		}

		parent::onStart($context);
	}

	public function __toString() {
		return sprintf('[IncidentActivity] #%d: %s', $this->id, $this->description);
	}

}