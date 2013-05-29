<?php
/**
 * Workflow.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 14.4.13 13:57
 */

namespace ITILSimulator\Entities\Workflow;


use Doctrine\Common\Collections\ArrayCollection;
use ITILSimulator\Entities\Training\Scenario;
use ITILSimulator\Entities\Workflow\Activities\StartActivity;
use Nette\Object;

/**
 * Workflow (Doctrine entity).
 * @Entity(repositoryClass="ITILSimulator\Repositories\Workflow\WorkflowRepository")
 * @Table(name="workflows")
 **/
class Workflow extends Object
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
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Training\Scenario")
	 * @var Scenario
	 */
	protected $scenario;

	/**
	 * @OneToMany(targetEntity="ITILSimulator\Entities\Workflow\WorkflowActivity", mappedBy="workflow", cascade="remove")
	 * @var ArrayCollection|WorkflowActivity[]
	 */
	protected $workflowActivities;

	#endregion

	public function __construct()
	{
		$this->workflowActivities = new ArrayCollection();
	}

	/**
	 * @return WorkflowActivity|null
	 */
	public function getStartActivity() {
		$startActivities = array_filter(
			$this->getWorkflowActivities()->toArray(),
			function($activity) {
				return $activity instanceof StartActivity;
			}
		);

		if (!count($startActivities))
			return NULL;

		return reset($startActivities);
	}

	/**
	 * @return int
	 */
	public function getTrainingId()
	{
		return $this->scenario->getTrainingId();
	}

	/**
	 * @return int
	 */
	public function getCreatorUserId()
	{
		return $this->scenario->getCreatorUserId();
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
	 * @param Scenario $scenario
	 */
	public function setScenario($scenario)
	{
		$this->scenario = $scenario;
		$scenario->addWorkflow($this);
	}

	/**
	 * @return Scenario
	 */
	public function getScenario()
	{
		return $this->scenario;
	}

	/**
	 * @return ArrayCollection|WorkflowActivity[]
	 */
	public function getWorkflowActivities()
	{
		return $this->workflowActivities;
	}

	#endregion
}