<?php
/**
 * Scenario.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 10.4.13 22:51
 */

namespace ITILSimulator\Entities\Training;

use Doctrine\Common\Collections\ArrayCollection;
use ITILSimulator\Entities\Session\ScenarioStep;
use ITILSimulator\Entities\Session\TrainingStep;
use ITILSimulator\Entities\Simulator\User;
use ITILSimulator\Entities\Workflow\Workflow;
use Nette\Object;

/**
 * Scenario (Doctrine entity).
 * @Entity(repositoryClass="ITILSimulator\Repositories\Training\ScenarioRepository")
 * @Table(name="scenarios")
 **/
class Scenario extends Object
{
	#region "Properties"

	/**
	 * @Id
	 * @Column(type="integer") @GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @Column(type="string",length=100)
	 * @var string
	 */
	protected $name;

	/**
	 * @Column(type="text")
	 * @var string
	 */
	protected $description;

	/**
	 * @Column(type="text")
	 * @var string
	 */
	protected $detailDescription;

	/**
	 * @Column(type="float")
	 * @var float
	 */
	protected $initialBudget = 0.0;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Training\Training", inversedBy="scenarios")
	 * @var Training
	 */
	protected $training;

	/**
	 * @ManyToMany(targetEntity="ITILSimulator\Entities\Training\Service")
	 * @JoinTable(name="services_per_scenarios",
	 *      joinColumns={@JoinColumn(name="scenario_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@JoinColumn(name="service_id", referencedColumnName="id")}
	 * )
	 * @var ArrayCollection|Service[]
	 */
	protected $services;

	/**
	 * @OneToMany(targetEntity="ITILSimulator\Entities\Workflow\Workflow", mappedBy="scenario", cascade="remove")
	 * @var ArrayCollection|Workflow[]
	 */
	protected $workflows;

	/**
	 * @OneToMany(targetEntity="ITILSimulator\Entities\Session\TrainingStep", mappedBy="scenario", cascade="remove")
	 * @var TrainingStep[]
	 */
	protected $trainingSteps;

	// TODO: not attribute, inheritance
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $type = 'operation';

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Training\Service")
	 * @var Service
	 */
	protected $designService;

	#endregion

	public function __construct() {
		$this->services = new ArrayCollection();
		$this->trainingSteps = new ArrayCollection();
		$this->workflows = new ArrayCollection();
	}

	/**
	 * @param Service $service
	 */
	public function assignService(Service $service) {
		$this->services->add($service);
	}

	/**
	 * @param Service $service
	 */
	public function unassignService(Service $service) {
		$this->services->removeElement($service);
	}

	#region "Get & set"

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
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
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
	 * @param float $initialBudget
	 */
	public function setInitialBudget($initialBudget)
	{
		$this->initialBudget = $initialBudget;
	}

	/**
	 * @return float
	 */
	public function getInitialBudget()
	{
		return $this->initialBudget;
	}

	/**
	 * @param \ITILSimulator\Entities\Training\Training $training
	 */
	public function setTraining($training)
	{
		$old = $this->training;
		$this->training = $training;

		if ($old && $old != $training) {
			$old->removeScenario($this);
		}

		if ($training && $old != $training) {
			$training->addScenario($this);
		}
	}

	/**
	 * @return \ITILSimulator\Entities\Training\Training
	 */
	public function getTraining()
	{
		return $this->training;
	}

	/**
	 * @return ArrayCollection|Service[]
	 */
	public function getServices()
	{
		return $this->services;
	}

	/**
	 * @return ArrayCollection|\ITILSimulator\Entities\Workflow\Workflow[]
	 */
	public function getWorkflows()
	{
		return $this->workflows;
	}

	/**
	 * @param Workflow $workflow
	 */
	public function addWorkflow(Workflow $workflow)
	{
		$this->workflows->add($workflow);
	}

	/**
	 * @param Workflow $workflow
	 */
	public function removeWorkflow(Workflow $workflow)
	{
		$this->workflows->removeElement($workflow);
	}

	/**
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return bool
	 */
	public function isDesign()
	{
		return $this->getIsDesign();
	}

	/**
	 * @return bool
	 */
	public function getIsDesign()
	{
		return $this->type == 'design';
	}

	/**
	 * @param \ITILSimulator\Entities\Training\Service $designService
	 */
	public function setDesignService($designService)
	{
		$this->designService = $designService;
	}

	/**
	 * @return \ITILSimulator\Entities\Training\Service
	 */
	public function getDesignService()
	{
		return $this->designService;
	}

	/**
	 * @param string $longDescription
	 */
	public function setDetailDescription($longDescription)
	{
		$this->detailDescription = $longDescription;
	}

	/**
	 * @return string
	 */
	public function getDetailDescription()
	{
		return $this->detailDescription;
	}

	#endregion

	#region "Inherited Get & set"

	/**
	 * @return int
	 */
	public function getTrainingId()
	{
		return $this->training->getId();
	}

	/**
	 * @return int
	 */
	public function getCreatorUserId()
	{
		return $this->training->getUserId();
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	public function isAvailableForUser($user)
	{
		return $this->training->isAvailableForUser($user);
	}

	#endregion



}