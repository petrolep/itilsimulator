<?php
/**
 * IncidentActivity.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 16.4.13 18:45
 */

namespace ITILSimulator\Entities\Workflow\Activities;


use ITILSimulator\Entities\OperationArtifact\OperationCategory;
use ITILSimulator\Entities\OperationArtifact\OperationProblem;
use ITILSimulator\Entities\Workflow\WorkflowActivity;
use ITILSimulator\Runtime\Events\EventTypeEnum;
use ITILSimulator\Runtime\Events\OperationProblemEvent;
use ITILSimulator\Runtime\RuntimeContext\WorkflowActivityRuntimeContext;

/**
 * Problem activity (Doctrine entity). Generates problems.
 * Inherits WorkflowActivity.
 * @Entity
 */
class ProblemActivity extends WorkflowActivity
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
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\OperationArtifact\OperationCategory")
	 * @JoinColumn(onDelete="SET NULL")
	 * @var OperationCategory
	 */
	protected $category;

	/**
	 * @Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $impact;

	/**
	 * @Column(type="string", length=100, nullable=true)
	 * @var string
	 */
	protected $problemOwner;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $symptoms;

	/**
	 * @Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $status;

	#endregion

	#region "Get & set"

	/**
	 * @param OperationCategory $category
	 */
	public function setCategory($category)
	{
		$this->category = $category;
	}

	/**
	 * @return OperationCategory
	 */
	public function getCategory()
	{
		return $this->category;
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

	#endregion

	/**
	 * Executed when the workflow activity starts. Fires ACTIVITY_PROBLEM_CREATED event.
	 * @param WorkflowActivityRuntimeContext $context
	 * @return mixed|null|void
	 */
	public function onStart(WorkflowActivityRuntimeContext $context) {
		$problem = new OperationProblem();
		$problem->setReferenceNumber($this->referenceNumber);
		$problem->setPriority($this->priority);
		$problem->setProblemOwner($this->problemOwner);
		$problem->setCategory($this->category);
		$problem->setSymptoms($this->symptoms);
		$problem->setStatus($this->status ? $this->status : OperationProblem::STATUS_NEW);

		$event = new OperationProblemEvent();
		$event->setOperationProblem($problem);
		$event->setDescription('');

		$context->getEventManager()->dispatch(EventTypeEnum::ACTIVITY_PROBLEM_CREATED, $event);

		if (!$this->onEvent) {
			// no condition on the flow, so just finish it
			$context->execute(WorkflowActivityRuntimeContext::FINISH_COMMAND, null);
		}

		parent::onStart($context);
	}

	public function __toString() {
		return sprintf('[ProblemActivity] #%d: %s', $this->id, $this->description);
	}

}