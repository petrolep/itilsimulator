<?php
/**
 * ServiceSpecification.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 10.4.13 22:18
 */

namespace ITILSimulator\Entities\Training;

use ITILSimulator\Runtime\Training\CustomServiceAttribute;
use Nette\Object;

/**
 * Service specification (Doctrine entity).
 * @Entity(repositoryClass="ITILSimulator\Repositories\Training\ServiceSpecificationRepository")
 * @Table(name="service_specifications")
 **/
class ServiceSpecification extends Object
{
	#region "Properties"

	/**
	 * @Id
	 * @Column(type="integer") @GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Training\Service", inversedBy="specifications")
	 * @var Service
	 */
	protected $service;

	/**
	 * @Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $priority;

	/**
	 * @Column(type="decimal")
	 * @var float
	 */
	protected $earnings = 0.0;

	/**
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $isDefault = false;

	/**
	 * @Column(type="text", nullable=true)
	 * @var CustomServiceAttribute[]
	 */
	protected $attributes = array();

	/** @var array CustomServiceAttribute[] */
	protected $attributesUnserialized = array();

	#endregion

	public function __construct(Service $service) {
		$this->service = $service;
	}

	#region "Get & set"

	/**
	 * @return \ITILSimulator\Entities\Training\Service
	 */
	public function getService()
	{
		return $this->service;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param float $earnings
	 */
	public function setEarnings($earnings)
	{
		$this->earnings = $earnings;
	}

	/**
	 * @return float
	 */
	public function getEarnings()
	{
		return $this->earnings;
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
	 * @param boolean $isDefault
	 */
	public function setIsDefault($isDefault)
	{
		$this->isDefault = $isDefault;
	}

	/**
	 * @return boolean
	 */
	public function getIsDefault()
	{
		return $this->isDefault;
	}

	/**
	 * @return bool
	 */
	public function isDefault()
	{
		return $this->getIsDefault();
	}

	/**
	 * @param CustomServiceAttribute[] $attributes
	 */
	public function setAttributes($attributes)
	{
		$this->attributesUnserialized = $attributes;
		$this->attributes = serialize($attributes);
	}

	/**
	 * @return CustomServiceAttribute[]
	 */
	public function getAttributes()
	{
		if (!$this->attributesUnserialized)
			$this->attributesUnserialized = @unserialize($this->attributes);

		if (!$this->attributesUnserialized)
			$this->attributesUnserialized = array();

		return $this->attributesUnserialized;
	}

	/**
	 * @param string $name
	 * @param CustomServiceAttribute $value
	 */
	public function setAttribute($name, $value)
	{
		$this->attributesUnserialized[$name] = $value;
		$this->setAttributes($this->attributesUnserialized);
	}

	/**
	 * @param $name
	 * @return CustomServiceAttribute|null
	 */
	public function getAttribute($name)
	{
		if (array_key_exists($name, $this->attributesUnserialized))
			return $this->attributesUnserialized[$name];

		return null;
	}

	#endregion
}