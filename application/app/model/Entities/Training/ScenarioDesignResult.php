<?php
/**
 * DesignScenarioResult.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 5.5.13 21:58
 */

namespace ITILSimulator\Entities\Training;


use ITILSimulator\Entities\Session\TrainingStep;
use Nette\Object;

/**
 * Scenario result for Design scenario types (Doctrine entity).
 * @Entity(repositoryClass="ITILSimulator\Repositories\Training\ScenarioDesignResultRepository")
 * @Table(name="scenario_design_result")
 */
class ScenarioDesignResult extends Object
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
	protected $comment;

	/**
	 * @Column(type="text", nullable=true)
	 * @var string
	 */
	protected $metadata;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Session\TrainingStep")
	 * @JoinColumn(onDelete="CASCADE")
	 * @var TrainingStep
	 */
	protected $trainingStep;

	/**
	 * @Column(type="float")
	 * @var float
	 */
	protected $purchaseCost;

	/**
	 * @Column(type="float")
	 * @var float
	 */
	protected $operationCost;

	#endregion

	#region "Get & set"
	/**
	 * @param string $comment
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
	}

	/**
	 * @return string
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $metadata
	 */
	public function setMetadata($metadata)
	{
		$this->metadata = $metadata;
	}

	/**
	 * @return string
	 */
	public function getMetadata()
	{
		return $this->metadata;
	}

	/**
	 * @param \ITILSimulator\Entities\Session\TrainingStep $trainingStep
	 */
	public function setTrainingStep($trainingStep)
	{
		$this->trainingStep = $trainingStep;
	}

	/**
	 * @return \ITILSimulator\Entities\Session\TrainingStep
	 */
	public function getTrainingStep()
	{
		return $this->trainingStep;
	}

	/**
	 * @param float $operationCost
	 */
	public function setOperationCost($operationCost)
	{
		$this->operationCost = $operationCost;
	}

	/**
	 * @return float
	 */
	public function getOperationCost()
	{
		return $this->operationCost;
	}

	/**
	 * @param float $purchaseCost
	 */
	public function setPurchaseCost($purchaseCost)
	{
		$this->purchaseCost = $purchaseCost;
	}

	/**
	 * @return float
	 */
	public function getPurchaseCost()
	{
		return $this->purchaseCost;
	}

	#endregion


}