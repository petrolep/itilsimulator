<?php
/**
 * ScenarioStep.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 11.4.13 19:24
 */

namespace ITILSimulator\Entities\Session;

use Doctrine\Common\Collections\ArrayCollection;
use ITILSimulator\Entities\Training\ConfigurationItemSpecification;
use ITILSimulator\Entities\Training\ServiceSpecification;
use ITILSimulator\Entities\Workflow\WorkflowActivitySpecification;
use Nette\Object;

/**
 * Scenario step class (Doctrine entity). Represents one step in a scenario.
 * @Entity(repositoryClass="ITILSimulator\Repositories\Session\ScenarioStepRepository")
 * @Table(name="scenario_steps")
 **/
class ScenarioStep extends Object
{
	#region "Properties"

	/**
	 * @Id
	 * @Column(type="integer") @GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @Column(type="datetime")
	 * @var \DateTime
	 */
	protected $date;

	/**
	 * @Column(type="datetime", nullable=true)
	 * @var \DateTime
	 */
	protected $undoDate = null;

	/**
	 * @Column(type="datetime", nullable=true)
	 * @var \DateTime
	 */
	protected $lastActivityDate = null;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Session\TrainingStep", inversedBy="scenarioSteps")
	 * @JoinColumn(onDelete="CASCADE")
	 * @var TrainingStep
	 */
	protected $trainingStep;

	/**
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $isUndid = false;

	/**
	 * @Column(type="integer")
	 * @var int
	 */
	protected $evaluationPoints = 0;

	/**
	 * @Column(type="float")
	 * @var float
	 */
	protected $budget = 0.0;

	/**
	 * @Column(type="integer")
	 * @var int
	 */
	protected $internalTime = 0;

	/**
	 * @Column(type="integer")
	 * @var int
	 */
	protected $servicesSettlementTime = 0;

	/**
	 * @ManyToMany(targetEntity="ITILSimulator\Entities\Training\ServiceSpecification")
	 * @JoinTable(name="service_specifications_per_scenario_steps",
	 *      joinColumns={@JoinColumn(name="scenario_step_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@JoinColumn(name="service_specification_id", referencedColumnName="id")}
	 * )
	 * @var ArrayCollection|ServiceSpecification[]
	 */
	protected $servicesSpecifications;

	/**
	 * @ManyToMany(targetEntity="ITILSimulator\Entities\Training\ConfigurationItemSpecification", inversedBy="scenarioSteps")
	 * @JoinTable(name="configuration_item_specifications_per_scenario_steps",
	 *      joinColumns={@JoinColumn(name="scenario_step_id", referencedColumnName="id", onDelete="CASCADE")},
	 *      inverseJoinColumns={@JoinColumn(name="configuration_item_specification_id", referencedColumnName="id", onDelete="CASCADE")}
	 * )
	 * @var ArrayCollection|ConfigurationItemSpecification[]
	 */
	protected $configurationItemsSpecifications;

	/**
	 * @ManyToMany(targetEntity="ITILSimulator\Entities\Workflow\WorkflowActivitySpecification", inversedBy="scenarioSteps")
	 * @JoinTable(name="workflow_activity_specifications_per_scenario_steps",
	 *      joinColumns={@JoinColumn(name="scenario_step_id", referencedColumnName="id", onDelete="CASCADE")},
	 *      inverseJoinColumns={@JoinColumn(name="workflow_activity_specification_id", referencedColumnName="id", onDelete="CASCADE")}
	 * )
	 * @var ArrayCollection|WorkflowActivitySpecification[]
	 */
	protected $workflowActivitiesSpecifications;

	#endregion

	public function __construct(TrainingStep $trainingStep) {
		$this->date = new \DateTime();
		$this->lastActivityDate = $this->date;
		$this->trainingStep = $trainingStep;

		$this->configurationItemsSpecifications = new ArrayCollection();
		$this->servicesSpecifications = new ArrayCollection();
		$this->workflowActivitiesSpecifications = new ArrayCollection();
	}

	/**
	 * Undo scenario step
	 */
	public function undo() {
		$this->isUndid = true;
		$this->undoDate = new \DateTime();
	}

	/**
	 * @return int
	 */
	public function getTrainingStepId()
	{
		return $this->trainingStep->getId();
	}

	/**
	 * @return int
	 */
	public function getTrainingId()
	{
		return $this->trainingStep->getTrainingId();
	}

	#region "Get & set"

	/**
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return boolean
	 */
	public function isUndid()
	{
		return $this->isUndid;
	}

	/**
	 * @return ArrayCollection|\ITILSimulator\Entities\Training\ServiceSpecification[]
	 */
	public function getServicesSpecifications()
	{
		return $this->servicesSpecifications;
	}

	/**
	 * @return ArrayCollection|\ITILSimulator\Entities\Training\ConfigurationItemSpecification[]
	 */
	public function getConfigurationItemsSpecifications()
	{
		return $this->configurationItemsSpecifications;
	}

	/**
	 * @return \ITILSimulator\Entities\Session\TrainingStep
	 */
	public function getTrainingStep()
	{
		return $this->trainingStep;
	}

	/**
	 * @param ServiceSpecification[] $serviceSpecifications
	 */
	public function setServicesSpecifications($serviceSpecifications)
	{
		$this->servicesSpecifications = $serviceSpecifications;
	}

	/**
	 * @param ConfigurationItemSpecification[] $configurationItemsSpecifications
	 */
	public function setConfigurationItemsSpecifications($configurationItemsSpecifications)
	{
		$this->configurationItemsSpecifications = $configurationItemsSpecifications;
	}

	/**
	 * @param WorkflowActivitySpecification[] $workflowActivitiesSpecifications
	 */
	public function setWorkflowActivitiesSpecifications($workflowActivitiesSpecifications)
	{
		$this->workflowActivitiesSpecifications = $workflowActivitiesSpecifications;
	}

	/**
	 * @return ArrayCollection|\ITILSimulator\Entities\Workflow\WorkflowActivitySpecification[]
	 */
	public function getWorkflowActivitiesSpecifications()
	{
		return $this->workflowActivitiesSpecifications;
	}

	/**
	 * @param int $evaluationPoints
	 */
	public function setEvaluationPoints($evaluationPoints)
	{
		$this->evaluationPoints = $evaluationPoints;
	}

	/**
	 * @return int
	 */
	public function getEvaluationPoints()
	{
		return $this->evaluationPoints;
	}

	/**
	 * @param float $budget
	 */
	public function setBudget($budget)
	{
		$this->budget = $budget;
	}

	/**
	 * @return float
	 */
	public function getBudget()
	{
		return $this->budget;
	}

	/**
	 * @param int $internalTime
	 */
	public function setInternalTime($internalTime)
	{
		$this->internalTime = $internalTime;
	}

	/**
	 * @return int
	 */
	public function getInternalTime()
	{
		return $this->internalTime;
	}

	/**
	 * @param \DateTime $undoDate
	 */
	public function setUndoDate($undoDate)
	{
		$this->undoDate = $undoDate;
	}

	/**
	 * @return \DateTime
	 */
	public function getUndoDate()
	{
		return $this->undoDate;
	}

	/**
	 * @param int $servicesSettlementTime
	 */
	public function setServicesSettlementTime($servicesSettlementTime)
	{
		$this->servicesSettlementTime = $servicesSettlementTime;
	}

	/**
	 * @return int
	 */
	public function getServicesSettlementTime()
	{
		return $this->servicesSettlementTime;
	}

	/**
	 * @param \DateTime $lastActivityDate
	 */
	public function setLastActivityDate($lastActivityDate)
	{
		$this->lastActivityDate = $lastActivityDate;
	}

	/**
	 * @return \DateTime
	 */
	public function getLastActivityDate()
	{
		if (!$this->lastActivityDate)
			return $this->date;

		return $this->lastActivityDate;
	}

	#endregion

}