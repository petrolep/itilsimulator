<?php
/**
 * OperationCategory.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 26.4.13 18:49
 */

namespace ITILSimulator\Entities\OperationArtifact;


use ITILSimulator\Entities\Training\KnownIssue;
use ITILSimulator\Entities\Training\Training;
use Nette\Object;

/**
 * Operation category Doctrine entity class. Inherits OperationArtifact.
 * @Entity(repositoryClass="ITILSimulator\Repositories\OperationArtifact\OperationCategoryRepository")
 * @Table(name="operation_categories")
 **/
class OperationCategory extends Object
{
	#region "Properties"

	/**
	 * @Id
	 * @Column(type="integer") @GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @Column(type="string", length=50, nullable=true)
	 * @var string
	 */
	protected $name;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Training\Training", inversedBy="operationalCategories")
	 * @var Training
	 */
	protected $training;

	/**
	 * @OneToMany(targetEntity="ITILSimulator\Entities\OperationArtifact\OperationProblem", cascade="remove", mappedBy="category")
	 * @var OperationProblem
	 */

	protected $operationalProblems;

	/**
	 * @OneToMany(targetEntity="ITILSimulator\Entities\Training\KnownIssue", cascade="remove", mappedBy="category")
	 * @var KnownIssue
	 */

	protected $knownIssues;

	#endregion

	public function getTrainingId()
	{
		return $this->training ? $this->training->getId() : 0;
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