<?php
/**
 * Workflow.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 13:57
 */

namespace ITILSimulator\Entities\Workflow;


use Doctrine\Common\Collections\ArrayCollection;
use ITILSimulator\Entities\Workflow\Activities\CustomActivity;
use ITILSimulator\Entities\Workflow\Activities\EvaluationActivity;
use ITILSimulator\Entities\Workflow\Activities\FinishActivity;
use ITILSimulator\Entities\Workflow\Activities\FlowActivity;
use ITILSimulator\Entities\Workflow\Activities\IncidentActivity;
use ITILSimulator\Entities\Workflow\Activities\MessageActivity;
use ITILSimulator\Entities\Workflow\Activities\ProblemActivity;
use ITILSimulator\Entities\Workflow\Activities\StartActivity;
use ITILSimulator\Runtime\Events\Event;
use ITILSimulator\Runtime\Events\EventTypeEnum;
use ITILSimulator\Runtime\RuntimeContext\WorkflowActivityRuntimeContext;
use Nette\Object;

/**
 * Workflow activity (Doctrine entity).
 * @Entity(repositoryClass="ITILSimulator\Repositories\Workflow\WorkflowActivityRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="activityType", type="string")
 * @DiscriminatorMap({
 *      "start"="ITILSimulator\Entities\Workflow\Activities\StartActivity",
 *      "message"="ITILSimulator\Entities\Workflow\Activities\MessageActivity",
 *      "incident"="ITILSimulator\Entities\Workflow\Activities\IncidentActivity",
 *      "problem"="ITILSimulator\Entities\Workflow\Activities\ProblemActivity",
 *      "evaluation"="ITILSimulator\Entities\Workflow\Activities\EvaluationActivity",
 *      "finish"="ITILSimulator\Entities\Workflow\Activities\FinishActivity",
 *      "custom"="ITILSimulator\Entities\Workflow\Activities\CustomActivity",
 *      "flow"="ITILSimulator\Entities\Workflow\Activities\FlowActivity",
 * })
 * @Table(name="workflow_activities")
 **/
abstract class WorkflowActivity extends Object
{
	#region "Properties"

	/**
	 * @Id
	 * @Column(type="integer") @GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $description;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Workflow\Workflow")
	 * @var Workflow
	 */
	protected $workflow;

	/**
	 * @OneToMany(targetEntity="ITILSimulator\Entities\Workflow\WorkflowActivitySpecification", mappedBy="workflowActivity", cascade={"remove"})
	 * @var ArrayCollection|WorkflowActivitySpecification[]
	 */
	protected $workflowActivitySpecifications;

	/**
	 * @ManyToMany(targetEntity="ITILSimulator\Entities\Workflow\WorkflowActivity", inversedBy="previousActivities")
	 * @JoinTable(name="workflow_activities_per_workflow_activities",
	 *      joinColumns={@JoinColumn(name="source_activity_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@JoinColumn(name="target_activity_id", referencedColumnName="id")}
	 * )
	 * @var ArrayCollection|WorkflowActivity[]
	 */
	protected $nextActivities;

	/**
	 * @ManyToMany(targetEntity="ITILSimulator\Entities\Workflow\WorkflowActivity", mappedBy="nextActivities")
	 * @var ArrayCollection|WorkflowActivity[]
	 **/
	protected $previousActivities;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $onEvent;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $onEventRaw;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $onStart;

	/**
	 * @Column(type="text", nullable=true)
	 * @var
	 */
	protected $onStartRaw;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $onCancel;

	/**
	 * @Column(type="text", nullable=true)
	 * @var
	 */
	protected $onCancelRaw;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $onFinish;

	/**
	 * @Column(type="text", nullable=true)
	 * @var
	 */
	protected $onFinishRaw;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $onFlow;

	/**
	 * @Column(type="text", nullable=true)
	 * @var
	 */
	protected $onFlowRaw;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $metadata;

	#endregion

	public function __construct() {
		$this->nextActivities = new ArrayCollection();
		$this->previousActivities = new ArrayCollection();
	}

	/**
	 * Return string representation of activity based on its type.
	 * Returns "start/finish/incident/problem/evaluation/flow/message/custom".
	 * @return string
	 */
	public function getStringType()
	{
		switch ($this) {
			case ($this instanceof StartActivity):
				return 'start';

			case ($this instanceof FinishActivity):
				return 'finish';

			case ($this instanceof IncidentActivity):
				return 'incident';

			case ($this instanceof ProblemActivity):
				return 'problem';

			case ($this instanceof EvaluationActivity):
				return 'evaluation';

			case ($this instanceof FlowActivity):
				return 'flow';

			case ($this instanceof MessageActivity):
				return 'message';

			case ($this instanceof CustomActivity):
				return 'custom';
		}

		return '';
	}

	/**
	 * @return int
	 */
	public function getCreatorUserId() {
		return $this->workflow->getCreatorUserId();
	}

	/**
	 * @return int
	 */
	public function getWorkflowId() {
		return $this->workflow->getId();
	}

	/**
	 * @return int
	 */
	public function getTrainingId() {
		return $this->workflow->getTrainingId();
	}

	#region "Get & Set"

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param Workflow $workflow
	 */
	public function setWorkflow($workflow)
	{
		$this->workflow = $workflow;
	}

	/**
	 * @return Workflow
	 */
	public function getWorkflow()
	{
		return $this->workflow;
	}

	/**
	 * @return ArrayCollection|WorkflowActivitySpecification[]
	 */
	public function getWorkflowActivitySpecifications()
	{
		return $this->workflowActivitySpecifications;
	}

	/**
	 * @param string $onEvent
	 */
	public function setOnEvent($onEvent)
	{
		$this->onEvent = $onEvent;
	}

	/**
	 * @return string
	 */
	public function getOnEvent()
	{
		return $this->onEvent;
	}

	/**
	 * @param string $onEventRaw
	 */
	public function setOnEventRaw($onEventRaw)
	{
		$this->onEventRaw = $onEventRaw;
	}

	/**
	 * @return string
	 */
	public function getOnEventRaw()
	{
		return $this->onEventRaw;
	}

	/**
	 * @param string $onStart
	 */
	public function setOnStart($onStart)
	{
		$this->onStart = $onStart;
	}

	/**
	 * @return string
	 */
	public function getOnStart()
	{
		return $this->onStart;
	}

	/**
	 * @param  $onStartRaw
	 */
	public function setOnStartRaw($onStartRaw)
	{
		$this->onStartRaw = $onStartRaw;
	}

	/**
	 * @return
	 */
	public function getOnStartRaw()
	{
		return $this->onStartRaw;
	}

	/**
	 * @param string $onCancel
	 */
	public function setOnCancel($onCancel)
	{
		$this->onCancel = $onCancel;
	}

	/**
	 * @return string
	 */
	public function getOnCancel()
	{
		return $this->onCancel;
	}

	/**
	 * @param mixed $onCancelRaw
	 */
	public function setOnCancelRaw($onCancelRaw)
	{
		$this->onCancelRaw = $onCancelRaw;
	}

	/**
	 * @return mixed
	 */
	public function getOnCancelRaw()
	{
		return $this->onCancelRaw;
	}

	/**
	 * @param string $onFinish
	 */
	public function setOnFinish($onFinish)
	{
		$this->onFinish = $onFinish;
	}

	/**
	 * @return string
	 */
	public function getOnFinish()
	{
		return $this->onFinish;
	}

	/**
	 * @param mixed $onFinishRaw
	 */
	public function setOnFinishRaw($onFinishRaw)
	{
		$this->onFinishRaw = $onFinishRaw;
	}

	/**
	 * @return mixed
	 */
	public function getOnFinishRaw()
	{
		return $this->onFinishRaw;
	}

	/**
	 * @param string $onFlow
	 */
	public function setOnFlow($onFlow)
	{
		$this->onFlow = $onFlow;
	}

	/**
	 * @return string
	 */
	public function getOnFlow()
	{
		return $this->onFlow;
	}

	/**
	 * @param mixed $onFlowRaw
	 */
	public function setOnFlowRaw($onFlowRaw)
	{
		$this->onFlowRaw = $onFlowRaw;
	}

	/**
	 * @return mixed
	 */
	public function getOnFlowRaw()
	{
		return $this->onFlowRaw;
	}

	/**
	 * @return ArrayCollection|WorkflowActivity[]
	 */
	public function getNextActivities()
	{
		return $this->nextActivities;
	}

	/**
	 * @param WorkflowActivity $activity
	 */
	public function addNextActivity(WorkflowActivity $activity)
	{
		$this->nextActivities->add($activity);
	}

	public function removeNextActivity(WorkflowActivity $activity)
	{
		$this->nextActivities->removeElement($activity);
	}

	/**
	 * @return ArrayCollection|WorkflowActivity[]
	 */
	public function getPreviousActivities()
	{
		return $this->previousActivities;
	}

	/**
	 * @param WorkflowActivity $activity
	 */
	public function addPreviousActivity(WorkflowActivity $activity)
	{
		$this->previousActivities->add($activity);
	}

	/**
	 * @param WorkflowActivity $activity
	 */
	public function removePreviousActivity(WorkflowActivity $activity)
	{
		$this->previousActivities->removeElement($activity);
	}

	/**
	 * @param ActivityMetadata $metadata
	 */
	public function setMetadata(ActivityMetadata $metadata)
	{
		$this->metadata = serialize($metadata);
	}

	/**
	 * @return ActivityMetadata
	 */
	public function getMetadata()
	{
		$data = null;
		if ($this->metadata)
			$data = @unserialize($this->metadata);

		return $data ? $data : new ActivityMetadata();
	}

	#endregion

	#region "Events"

	/**
	 * Executed when the workflow activity starts
	 * @param WorkflowActivityRuntimeContext $context
	 * @return mixed|null
	 */
	public function onStart(WorkflowActivityRuntimeContext $context) {
		if ($this->onStart)
			return $context->execute($this->onStart, null);
	}

	/**
	 * Executed when the workflow activity finishes
	 * @param WorkflowActivityRuntimeContext $context
	 */
	public function onFinish(WorkflowActivityRuntimeContext $context) {
		// override
	}

	/**
	 * Executed when the workflow activity is canceled
	 * @param WorkflowActivityRuntimeContext $context
	 */
	public function onCancel(WorkflowActivityRuntimeContext $context) {
		// override
	}

	/**
	 * Executed when incoming Event is received
	 * @param WorkflowActivityRuntimeContext $context
	 * @param Event $event
	 * @return mixed|null
	 */
	public function onEvent(WorkflowActivityRuntimeContext $context, Event $event) {
		$ignoredEvents = array(
			EventTypeEnum::CONFIGURATION_ITEM_CHANGE,
			EventTypeEnum::CONFIGURATION_ITEM_REQUEST,
			EventTypeEnum::ACTIVITY_INCIDENT_CHANGE,
			EventTypeEnum::ACTIVITY_PROBLEM_CHANGE
		);

		if ($event && in_array($event->getType(), $ignoredEvents))
			return NULL;

		if ($this->onEvent)
			return $context->execute($this->onEvent, $event);
	}

	/**
	 * Executed when workflow flow enters the activity
	 * @param WorkflowActivityRuntimeContext $context
	 */
	public function onFlow(WorkflowActivityRuntimeContext $context) {
		// override
	}

	/**
	 * Executed when running activity is restored (during HTTP request)
	 * @param WorkflowActivityRuntimeContext $context
	 */
	public function onRestore(WorkflowActivityRuntimeContext $context) {
		// override
	}

	#endregion

	public function __toString() {
		return sprintf('[%s] #%d: %s', get_class($this), $this->id, $this->description);
	}
}