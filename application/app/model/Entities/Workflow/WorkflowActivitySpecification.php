<?php
/**
 * Workflow.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 13:57
 */

namespace ITILSimulator\Entities\Workflow;


use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Entities\Workflow\Activities\StartActivity;
use ITILSimulator\Runtime\Workflow\WorkflowActivityStateEnum;
use Nette\Object;

/**
 * Workflow activity specification (Doctrine entity).
 * @Entity(repositoryClass="ITILSimulator\Repositories\Workflow\WorkflowActivitySpecificationRepository")
 * @Table(name="workflow_activity_specifications")
 **/
class WorkflowActivitySpecification extends Object
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
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Workflow\WorkflowActivity")
	 * @var WorkflowActivity
	 */
	protected $workflowActivity;

	/**
	 * @Column(type="integer")
	 * @var int
	 */
	protected $state = 0;

	/**
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $isDefault = false;

	/**
	 * @ManyToMany(targetEntity="ITILSimulator\Entities\Session\ScenarioStep", mappedBy="workflowActivitiesSpecifications", cascade="remove")
	 * @var ScenarioStep[]
	 */
	protected $scenarioSteps;

	/**
	 * @Column(type="array", nullable=true)
	 * @var array
	 */
	protected $data = array();

	#endregion

	/**
	 * @param WorkflowActivity $workflowActivity
	 */
	public function __construct(WorkflowActivity $workflowActivity)
	{
		$this->workflowActivity = $workflowActivity;
		$this->state = ($workflowActivity instanceof StartActivity ? WorkflowActivityStateEnum::RUNNING : WorkflowActivityStateEnum::WAITING);
	}

	/**
	 * @return bool
	 */
	public function isFinished() {
		return $this->getState() === WorkflowActivityStateEnum::FINISHED;
	}

	/**
	 * @return bool
	 */
	public function isRunning() {
		return $this->getState() === WorkflowActivityStateEnum::RUNNING;
	}

	/**
	 * @return bool
	 */
	public function isWaiting() {
		return $this->getState() === WorkflowActivityStateEnum::WAITING;
	}

	/**
	 * @param WorkflowActivitySpecification $other
	 * @return bool
	 */
	public function equals(WorkflowActivitySpecification $other)
	{
		$fields = array('state');
		foreach ($fields as $field) {
			if ($this->$field != $other->$field)
				return false;
		}

		// compare data
		return (serialize($this->getData()) == serialize($other->getData()));
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
	 * @return \ITILSimulator\Entities\Workflow\WorkflowActivity
	 */
	public function getWorkflowActivity()
	{
		return $this->workflowActivity;
	}

	/**
	 * @param \ITILSimulator\Entities\Workflow\WorkflowActivity $workflowActivity
	 */
	public function setWorkflowActivity($workflowActivity)
	{
		$this->workflowActivity = $workflowActivity;
	}


	/**
	 * @param boolean $isDefault
	 */
	public function setDefault($isDefault)
	{
		$this->isDefault = $isDefault;
	}

	/**
	 * @return boolean
	 */
	public function isDefault()
	{
		return $this->isDefault;
	}

	/**
	 * @param int $state
	 */
	public function setState($state)
	{
		$this->state = $state;
	}

	/**
	 * @return int
	 */
	public function getState()
	{
		return $this->state;
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
	 * @param $key
	 * @param $value
	 */
	public function setDataValue($key, $value)
	{
		if (!is_array($this->data))
			$this->data = array();

		$this->data[$key] = $value;
	}

	/**
	 * @param string|null $key
	 * @return array|null
	 */
	public function getData($key = NULL)
	{
		if (!is_array($this->data))
			$this->data = array();

		if (!$key)
			return $this->data;

		if (!array_key_exists($key, $this->data))
			return NULL;

		return $this->data[$key];
	}

	#endregion
}