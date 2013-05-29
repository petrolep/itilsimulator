<?php
/**
 * KnownIssue.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 28.4.13 22:28
 */

namespace ITILSimulator\Entities\Training;


use ITILSimulator\Entities\OperationArtifact\OperationCategory;
use Nette\Object;
/**
 * Known issue (error) (Doctrine entity).
 * @Entity(repositoryClass="ITILSimulator\Repositories\Training\KnownIssueRepository")
 * @Table(name="known_issues")
 */
class KnownIssue extends Object
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
	 * @Column(type="string", length=200, nullable=true)
	 * @var string
	 */
	protected $keywords;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $description;

	/**
	 * @Column(type="string", length=255, nullable=true)
	 * @var string
	 */
	protected $workaround;

	/**
	 * @Column(type="float")
	 * @var float
	 */
	protected $workaroundCost = 0.0;

	/**
	 * @Column(type="string", length=255, nullable=true)
	 * @var string
	 */
	protected $fix;

	/**
	 * @Column(type="float")
	 * @var float
	 */
	protected $fixCost = 0.0;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Training\Training")
	 * @var Training
	 */
	protected $training;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\OperationArtifact\OperationCategory", inversedBy="knownIssues")
	 * @JoinColumn(onDelete="SET NULL")
	 * @var OperationCategory
	 */
	protected $category;

	#endregion

	/**
	 * @return int
	 */
	public function getCategoryId()
	{
		if ($this->category)
			return $this->category->getId();

		return 0;
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
	 * @param \ITILSimulator\Entities\OperationArtifact\OperationCategory $category
	 */
	public function setCategory($category)
	{
		$this->category = $category;
	}

	/**
	 * @return \ITILSimulator\Entities\OperationArtifact\OperationCategory
	 */
	public function getCategory()
	{
		return $this->category;
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
	 * @param string $fix
	 */
	public function setFix($fix)
	{
		$this->fix = $fix;
	}

	/**
	 * @return string
	 */
	public function getFix()
	{
		return $this->fix;
	}

	/**
	 * @param float $fixCost
	 */
	public function setFixCost($fixCost)
	{
		$this->fixCost = $fixCost;
	}

	/**
	 * @return float
	 */
	public function getFixCost()
	{
		return $this->fixCost;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $keywords
	 */
	public function setKeywords($keywords)
	{
		$this->keywords = $keywords;
	}

	/**
	 * @return string
	 */
	public function getKeywords()
	{
		return $this->keywords;
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
	 * @param \ITILSimulator\Entities\Training\Training $training
	 */
	public function setTraining($training)
	{
		$old = $this->training;
		$this->training = $training;

		if ($old && $old != $training)
			$old->removeKnownIssue($this);

		if ($training && $old != $training)
			$training->addKnownIssue($this);
	}

	/**
	 * @return \ITILSimulator\Entities\Training\Training
	 */
	public function getTraining()
	{
		return $this->training;
	}

	/**
	 * @param string $workaround
	 */
	public function setWorkaround($workaround)
	{
		$this->workaround = $workaround;
	}

	/**
	 * @return string
	 */
	public function getWorkaround()
	{
		return $this->workaround;
	}

	/**
	 * @param float $workaroundCost
	 */
	public function setWorkaroundCost($workaroundCost)
	{
		$this->workaroundCost = $workaroundCost;
	}

	/**
	 * @return float
	 */
	public function getWorkaroundCost()
	{
		return $this->workaroundCost;
	}

	#endregion


}