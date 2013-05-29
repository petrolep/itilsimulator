<?php
/**
 * Service.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 7.4.13 13:59
 */

namespace ITILSimulator\Entities\Training;

use Doctrine\Common\Collections\ArrayCollection;
use Nette\Object;

/**
 * Service (Doctrine entity).
 * @Entity(repositoryClass="ITILSimulator\Repositories\Training\ServiceRepository")
 * @Table(name="services")
 **/
class Service extends Object
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
	 * @Column(type="string", length=30)
	 * @var string
	 */
	protected $code;

	/**
	 * @Column(type="text")
	 * @var string
	 */
	protected $description;

	/**
	 * @Column(type="string",length=30)
	 * @var string
	 */
	protected $serviceOwner;

	/**
	 * @Column(type="text")
	 * @var string
	 */
	protected $graphicDesignData;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Training\Training", inversedBy="services")
	 * @var Training
	 */
	protected $training;

	/**
	 * @ManyToMany(targetEntity="ITILSimulator\Entities\Training\ConfigurationItem", inversedBy="services")
	 * @JoinTable(name="configuration_items_per_services",
	 *      joinColumns={@JoinColumn(name="service_id", referencedColumnName="id")},
	 *      inverseJoinColumns={@JoinColumn(name="configuration_item_id", referencedColumnName="id")}
	 * )
	 * @OrderBy({"name" = "ASC"})
	 * @var ArrayCollection|ConfigurationItem[]
	 */
	protected $configurationItems;

	/**
	 * @OneToMany(targetEntity="ITILSimulator\Entities\Training\ServiceSpecification", mappedBy="service", cascade={"remove"})
	 * @var ArrayCollection|ServiceSpecification[]
	 */
	protected $serviceSpecifications;

	/**
	 * @var ServiceSpecification
	 */
	private $defaultSpecification;

	#endregion

	public function __construct()
	{
		$this->configurationItems = new ArrayCollection();
		$this->serviceSpecifications = new ArrayCollection();
	}

	/**
	 * @param ConfigurationItem $ci
	 */
	public function addConfigurationItem(ConfigurationItem $ci) {
		if (!$this->configurationItems->contains($ci))
			$this->configurationItems->add($ci);
	}

	/**
	 * @param ConfigurationItem $ci
	 */
	public function removeConfigurationItem(ConfigurationItem $ci) {
		$this->configurationItems->removeElement($ci);
		$ci->removeService($this);
	}

	/**
	 * @return ServiceSpecification
	 */
	public function getDefaultSpecification() {
		if (!$this->defaultSpecification) {
			foreach ($this->serviceSpecifications as $specification) {
				if ($specification->isDefault()) {
					$this->defaultSpecification = $specification;

					break;
				}
			}
		}

		return $this->defaultSpecification;
	}

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
	public function getUserId()
	{
		return $this->training->getUserId();
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
	 * @param string $graphicDesignData
	 */
	public function setGraphicDesignData($graphicDesignData)
	{
		$this->graphicDesignData = $graphicDesignData;
	}

	/**
	 * @return string
	 */
	public function getGraphicDesignData()
	{
		return $this->graphicDesignData;
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
	 * @param string $serviceOwner
	 */
	public function setServiceOwner($serviceOwner)
	{
		$this->serviceOwner = $serviceOwner;
	}

	/**
	 * @return string
	 */
	public function getServiceOwner()
	{
		return $this->serviceOwner;
	}

	/**
	 * @param $training Training
	 */
	public function setTraining($training)
	{
		$old = $this->training;
		$this->training = $training;

		if ($old && $old != $training)
			$old->removeService($this);

		if ($training && $old != $training)
			$training->addService($this);
	}

	/**
	 * @return Training
	 */
	public function getTraining()
	{
		return $this->training;
	}

	/**
	 * @return ArrayCollection|ConfigurationItem[]
	 */
	public function getConfigurationItems()
	{
		return $this->configurationItems;
	}

	/**
	 * @return ArrayCollection|ServiceSpecification[]
	 */
	public function getServiceSpecifications()
	{
		return $this->serviceSpecifications;
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

	#endregion


}