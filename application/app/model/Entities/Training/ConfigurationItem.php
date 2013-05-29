<?php
/**
 * ConfigurationItem.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 7.4.13 19:55
 */

namespace ITILSimulator\Entities\Training;


use Doctrine\Common\Collections\ArrayCollection;
use Nette\Object;

/**
 * Configuration item (Doctrine entity).
 * @Entity(repositoryClass="ITILSimulator\Repositories\Training\ConfigurationItemRepository")
 * @Table(name="configuration_items")
 **/
class ConfigurationItem extends Object
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
	 * @Column(type="string", length=100)
	 * @var string
	 */
	protected $code;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $description;

	/**
	 * @ManyToMany(targetEntity="ITILSimulator\Entities\Training\Service", mappedBy="configurationItems")
	 * @var Service[]
	 */
	protected $services;

	/**
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $isGlobal = false;

	/**
	 * @Column(type="float", nullable=true)
	 * @var float
	 */
	protected $expectedReliability;

	/**
	 * @ManyToMany(targetEntity="ITILSimulator\Entities\Training\InputOutput", cascade="remove", inversedBy="configurationItemsInputs")
	 * @JoinTable(name="io_input_per_configuration_item",
	 *      joinColumns={@JoinColumn(name="configuration_item_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@JoinColumn(name="input_output_id", referencedColumnName="id")}
	 * )
	 * @OrderBy({"name" = "ASC"})
	 * @var InputOutput[]
	 */
	protected $inputs;

	/**
	 * @ManyToMany(targetEntity="ITILSimulator\Entities\Training\InputOutput", inversedBy="configurationItemsOutputs")
	 * @JoinTable(name="io_output_per_configuration_item",
	 *      joinColumns={@JoinColumn(name="configuration_item_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@JoinColumn(name="input_output_id", referencedColumnName="id")}
	 * )
	 * @OrderBy({"name" = "ASC"})
	 * @var InputOutput[]
	 */
	protected $outputs;

	/**
	 * @OneToMany(targetEntity="ITILSimulator\Entities\Training\ConfigurationItemSpecification", mappedBy="configurationItem", cascade="remove")
	 * @var ConfigurationItemSpecification[]
	 */
	protected $specifications;

	/**
	 * @var ConfigurationItemSpecification
	 */
	private $defaultSpecification = null;

	#endregion

	public function __construct() {
		$this->services = new ArrayCollection();
		$this->specifications = new ArrayCollection();
		$this->inputs = new ArrayCollection();
		$this->outputs = new ArrayCollection();
	}

	/**
	 * Default specification of configuration item (defined by creator)
	 * @return ConfigurationItemSpecification|null
	 */
	public function getDefaultSpecification() {
		if (!$this->defaultSpecification) {
			foreach ($this->specifications as $specification) {
				if ($specification->isDefault()) {
					$this->defaultSpecification = $specification;
				}
			}
		}

		return $this->defaultSpecification;
	}

	#region "Get & set"

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
	 * @return ArrayCollection|Service[]
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
		$service->addConfigurationItem($this);
		$this->services->add($service);
	}

	/**
	 * @param Service $service
	 */
	public function removeService(Service $service)
	{
		$this->services->removeElement($service);
	}

	public function clearServices()
	{
		foreach ($this->services as $service) {
			$service->removeConfigurationItem($this);
		}

		$this->services->clear();
	}

	/**
	 * @param boolean $isGlobal
	 */
	public function setGlobal($isGlobal)
	{
		$this->isGlobal = $isGlobal;
	}

	/**
	 * @param boolean $isGlobal
	 */
	public function setIsGlobal($isGlobal) {
		$this->setGlobal($isGlobal);
	}

	/**
	 * @return boolean
	 */
	public function isGlobal()
	{
		return $this->getIsGlobal();
	}

	/**
	 * @return bool
	 */
	public function getIsGlobal()
	{
		return $this->isGlobal;
	}

	/**
	 * @return ArrayCollection|ConfigurationItemSpecification[]
	 */
	public function getSpecifications()
	{
		return $this->specifications;
	}

	/**
	 * @return ArrayCollection|InputOutput[]
	 */
	public function getInputs()
	{
		return $this->inputs;
	}

	/**
	 * @param InputOutput $input
	 */
	public function addInput(InputOutput $input)
	{
		$this->inputs->add($input);
	}

	/**
	 * @param InputOutput $input
	 */
	public function removeInput(InputOutput $input)
	{
		$this->inputs->removeElement($input);
	}

	public function clearInputs()
	{
		$this->inputs->clear();
	}

	/**
	 * @return ArrayCollection|InputOutput[]
	 */
	public function getOutputs()
	{
		return $this->outputs;
	}

	/**
	 * @param InputOutput $output
	 */
	public function addOutput(InputOutput $output)
	{
		$this->outputs->add($output);
	}

	/**
	 * @param InputOutput $output
	 */
	public function removeOutput(InputOutput $output)
	{
		$this->outputs->removeElement($output);
	}

	public function clearOutputs()
	{
		$this->outputs->clear();
	}

	/**
	 * @param string $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param float $expectedReliability
	 */
	public function setExpectedReliability($expectedReliability)
	{
		$this->expectedReliability = $expectedReliability;
	}

	/**
	 * @return float
	 */
	public function getExpectedReliability()
	{
		return $this->expectedReliability;
	}

	#endregion

}