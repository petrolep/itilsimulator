<?php
/**
 * InputOutput.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 21.4.13 18:53
 */

namespace ITILSimulator\Entities\Training;


use Doctrine\Common\Collections\ArrayCollection;
use Nette\Object;

/**
 * Input/Output (Doctrine entity).
 * @Entity(repositoryClass="ITILSimulator\Repositories\Training\InputOutputRepository")
 * @Table(name="inputs_outputs")
 */
class InputOutput extends Object
{
	#region "Properties"

	/**
	 * @Id
	 * @Column(type="integer") @GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @Column(type="string", length=30)
	 * @var string
	 */
	protected $name;

	/**
	 * @Column(type="string", length=30)
	 * @var string
	 */
	protected $code;

	/**
	 * @ManyToMany(targetEntity="ITILSimulator\Entities\Training\ConfigurationItem", mappedBy="inputs")
	 * @var ConfigurationItem[]
	 */
	protected $configurationItemsInputs;

	/**
	 * @ManyToMany(targetEntity="ITILSimulator\Entities\Training\ConfigurationItem", mappedBy="outputs")
	 * @var ConfigurationItem[]
	 */
	protected $configurationItemsOutputs;


	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Training\Training")
	 * @var Training
	 */
	protected $training;

	#endregion

	public function __construct()
	{
		$this->configurationItemsInputs = new ArrayCollection();
		$this->configurationItemsOutputs = new ArrayCollection();
	}

	/**
	 * @return int
	 */
	public function getTrainingId()
	{
		return $this->training->getId();
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
	 * @param \ITILSimulator\Entities\Training\Training $training
	 */
	public function setTraining($training)
	{
		$this->training = $training;
	}

	/**
	 * @return \ITILSimulator\Entities\Training\Training
	 */
	public function getTraining()
	{
		return $this->training;
	}

	#endregion

}