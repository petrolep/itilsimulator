<?php
/**
 * Training.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 7.4.13 13:54
 */

namespace ITILSimulator\Entities\Training;

use Doctrine\Common\Collections\ArrayCollection;
use ITILSimulator\Entities\OperationArtifact\OperationCategory;
use ITILSimulator\Entities\Simulator\User;
use Nette\Object;

/**
 * Training (Doctrine entity).
 * @Entity(repositoryClass="ITILSimulator\Repositories\Training\TrainingRepository")
 * @Table(name="trainings")
 **/
class Training extends Object
{
	#region "Properties"

	/**
	 * @Id
	 * @Column(type="integer") @GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @Column(type="string", length=100)
	 * @var string
	 */
	protected $name;

	/**
	 * @Column(type="string", length=255, nullable=true)
	 * @var string
	 */
	protected $shortDescription;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $description;

	/**
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $isPublished = false;

	/**
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $isPublic = false;

	/**
	 * @OneToMany(targetEntity="\ITILSimulator\Entities\Training\Service", mappedBy="training", cascade={"remove"})
	 * @var ArrayCollection|Training[]
	 **/
	protected $services = null;

	/**
	 * @OneToMany(targetEntity="\ITILSimulator\Entities\Training\Scenario", mappedBy="training", cascade={"persist", "remove"})
	 * @var ArrayCollection|Scenario[]
	 **/
	protected $scenarios = null;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Simulator\User")
	 * @JoinColumn(onDelete="CASCADE")
	 * @var User
	 */
	protected $user;

	/**
	 * @OneToMany(targetEntity="ITILSimulator\Entities\OperationArtifact\OperationCategory", mappedBy="training", cascade={"remove"})
	 * @var ArrayCollection|OperationCategory[]
	 */
	protected $operationCategories;

	/**
	 * @OneToMany(targetEntity="ITILSimulator\Entities\Training\InputOutput", mappedBy="training", cascade={"remove"})
	 * @OrderBy({"name" = "ASC"})
	 * @var ArrayCollection|InputOutput[]
	 */
	protected $inputsOutputs;

	/**
	 * @OneToMany(targetEntity="ITILSimulator\Entities\Training\KnownIssue", mappedBy="training", cascade={"remove"})
	 * @var ArrayCollection|KnownIssue[]
	 */
	protected $knownIssues;

	#endregion

	public function __construct()
	{
		$this->scenarios = new ArrayCollection();
		$this->services = new ArrayCollection();
		$this->operationCategories = new ArrayCollection();
		$this->inputsOutputs = new ArrayCollection();
		$this->knownIssues = new ArrayCollection();
	}

	/**
	 * @param User $user
	 * @return bool
	 */
	public function isAvailableForUser($user) {
		if (!$user || !($user instanceof User))
			return $this->isPublic() && $this->isPublished();

		/** @var $user User */
		if (!$user->isAnonymous())
			return $this->getIsPublished();

		return $this->getIsPublished() && ($this->isPublic() || $this->user->getId() == $user->getId());
	}

	/**
	 * @return int
	 */
	public function getUserId()
	{
		return $this->user->getId();
	}

	/**
	 * @return bool
	 */
	public function isCreatedByAnonymousUser() {
		return $this->user->isAnonymous();
	}

	#region "Get & set"

	/**
	 * @param bool $isPublic
	 */
	public function setIsPublic($isPublic)
	{
		$this->isPublic = $isPublic;
	}

	/**
	 * @return bool
	 */
	public function getIsPublic()
	{
		return $this->isPublic;
	}

	/**
	 * @return bool
	 */
	public function isPublic()
	{
		return $this->getIsPublic();
	}

	/**
	 * @param boolean $isPublished
	 */
	public function setIsPublished($isPublished)
	{
		$this->isPublished = $isPublished;
	}

	/**
	 * @return boolean
	 */
	public function getIsPublished()
	{
		return $this->isPublished;
	}

	/**
	 * @return bool
	 */
	public function isPublished()
	{
		return $this->getIsPublished();
	}

	/**
	 * @param $name
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
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return ArrayCollection|Training[]|null
	 */
	public function getServices()
	{
		return $this->services;
	}

	/**
	 * @param Service $service
	 */
	public function addService(Service $service)
	{
		$this->services->add($service);
		if ($service->getTraining() != $this)
			$service->setTraining($this);
	}

	/**
	 * @param Service $service
	 */
	public function removeService(Service $service)
	{
		$this->services->removeElement($service);
		if ($service->getTraining() == $this)
			$service->setTraining(null);
	}

	/**
	 * @param \ITILSimulator\Entities\Simulator\User $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

	/**
	 * @return \ITILSimulator\Entities\Simulator\User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @return ArrayCollection|Scenario[]
	 */
	public function getScenarios()
	{
		return $this->scenarios;
	}

	/**
	 * @param Scenario $scenario
	 */
	public function addScenario(Scenario $scenario)
	{
		$this->scenarios->add($scenario);
		if ($scenario->getTraining() != $this)
			$scenario->setTraining($this);
	}

	/**
	 * @param Scenario $scenario
	 */
	public function removeScenario(Scenario $scenario)
	{
		$this->scenarios->removeElement($scenario);
		if ($scenario->getTraining() == $this)
			$scenario->setTraining(null);
	}

	/**
	 * @param int $id
	 * @return Scenario
	 */
	public function getScenario($id) {
		foreach ($this->scenarios as $scenario) {
			if ($scenario->getId() == $id) {
				return $scenario;
			}
		}
	}

	/**
	 * @param string $shortDescription
	 */
	public function setShortDescription($shortDescription)
	{
		$this->shortDescription = $shortDescription;
	}

	/**
	 * @return string
	 */
	public function getShortDescription()
	{
		return $this->shortDescription;
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
	 * @return ArrayCollection|OperationCategory[]
	 */
	public function getOperationCategories()
	{
		return $this->operationCategories;
	}

	/**
	 * @param OperationCategory $category
	 */
	public function addOperationCategory(OperationCategory $category)
	{
		$this->operationCategories->add($category);
	}

	/**
	 * @param OperationCategory $category
	 */
	public function removeOperationCategory(OperationCategory $category)
	{
		$this->operationCategories->removeElement($category);
	}

	/**
	 * @return ArrayCollection|InputOutput[]
	 */
	public function getInputsOutputs()
	{
		return $this->inputsOutputs;
	}

	/**
	 * @param InputOutput $io
	 */
	public function addInputOutput(InputOutput $io)
	{
		$this->inputsOutputs->add($io);
	}

	/**
	 * @param InputOutput $io
	 */
	public function removeInputOutput(InputOutput $io)
	{
		$this->inputsOutputs->removeElement($io);
	}

	/**
	 * @return ArrayCollection|KnownIssue[]
	 */
	public function getKnownIssues()
	{
		return $this->knownIssues;
	}

	/**
	 * @param KnownIssue $issue
	 */
	public function addKnownIssue(KnownIssue $issue)
	{
		$this->knownIssues->add($issue);
		if ($issue->getTraining() != $this)
			$issue->setTraining($this);
	}

	/**
	 * @param KnownIssue $issue
	 */
	public function removeKnownIssue(KnownIssue $issue)
	{
		$this->knownIssues->removeElement($issue);
		if ($issue->getTraining() == $this)
			$issue->setTraining(null);
	}

	#endregion


}