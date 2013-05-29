<?php
/**
 * TrainingStep.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 10.4.13 22:49
 */

namespace ITILSimulator\Entities\Session;


use Doctrine\Common\Collections\ArrayCollection;
use ITILSimulator\Entities\Session\Session;
use ITILSimulator\Entities\Simulator\User;
use ITILSimulator\Entities\Training\Scenario;
use Nette\InvalidStateException;
use Nette\Object;

/**
 * Training step class (Doctrine entity). Represents one step in a training, e.g. one scenario.
 * @Entity(repositoryClass="ITILSimulator\Repositories\Session\TrainingStepRepository")
 * @Table(name="training_steps")
 **/
class TrainingStep extends Object
{
	#region "Properties"

	/**
	 * @Id
	 * @Column(type="integer") @GeneratedValue
	 * @var int
	 */
	protected $id;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Session\Session")
	 * @var Session
	 */
	protected $session;

	/**
	 * @ManyToOne(targetEntity="ITILSimulator\Entities\Training\Scenario")
	 * @var Scenario
	 */
	protected $scenario;

	/**
	 * @Column(type="datetime")
	 * @var \DateTime
	 */
	protected $dateStart;

	/**
	 * @Column(type="datetime")
	 * @var \DateTime
	 */
	protected $dateEnd;

	/**
	 * @Column(type="boolean")
	 * @var bool
	 */
	protected $isFinished = false;

	/**
	 * @Column(type="integer", nullable=true)
	 * @var int
	 */
	protected $evaluationPoints = null;

	/**
	 * @Column(type="float", nullable=true)
	 * @var float
	 */
	protected $budget = null;

	/**
	 * @OneToMany(targetEntity="ITILSimulator\Entities\Session\ScenarioStep", mappedBy="trainingStep")
	 * @var ArrayCollection|ScenarioStep[]
	 */
	protected $scenarioSteps;

	#endregion

	public function __construct() {
		$this->dateStart = new \DateTime();
		$this->dateEnd = $this->dateStart;

		$this->scenarioSteps = new ArrayCollection();
	}

	/**
	 * Return last valid scenario step (from which the scenario should continue)
	 * @return ScenarioStep
	 */
	public function getLastValidScenarioStep() {
		/** @var $stepsCollection ArrayCollection */
		$stepsCollection = $this->scenarioSteps;
		$iterator = $stepsCollection
						->filter(function($step) { return !$step->isUndid();})
						->getIterator();

		$iterator->uasort(function ($first, $second) {
			if ($first === $second) {
				return 0;
			}

			return $first->id < $second->id ? -1 : 1;
		});

		if (!$iterator->count())
			return NULL;

		return end($iterator);
	}

	/**
	 * Return scenario ID
	 * @return int
	 */
	public function getScenarioId() {
		return $this->scenario->getId();
	}

	/**
	 * Return training ID
	 * @return int
	 */
	public function getTrainingId() {
		return $this->scenario->getTrainingId();
	}

	/**
	 * Finish training step
	 * @throws \Nette\InvalidStateException
	 */
	public function finish() {
		if ($this->isFinished()) {
			throw new InvalidStateException('Training step already finished.');
		}

		$scenarioStep = $this->getLastValidScenarioStep();
		if ($scenarioStep) {
			$this->setBudget($scenarioStep->getBudget());
			$this->setEvaluationPoints($scenarioStep->getEvaluationPoints());
		}
		$this->setDateEnd(new \DateTime());
		$this->isFinished = true;
	}

	/**
	 * Return user ID of session (player)
	 * @return int
	 */
	public function getUserId() {
		return $this->session->getUserId();
	}

	public function getTraining() {
		return $this->scenario->getTraining();
	}

	/**
	 * Return TRUE if the scenario is Service Design scenario
	 * @return bool
	 */
	public function isDesign() {
		return $this->scenario->isDesign();
	}

	/**
	 * Return TRUE if the training is available for selected user
	 * @param User $user
	 * @return bool
	 */
	public function isAvailableForUser($user) {
		return $this->getTraining()->isAvailableForUser($user);
	}

	/**
	 * Return session ID
	 * @return int
	 */
	public function getSessionId() {
		return $this->session->getId();
	}

	#region "Get & set"

	/**
	 * @param \DateTime $dateEnd
	 */
	public function setDateEnd($dateEnd)
	{
		$this->dateEnd = $dateEnd;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateEnd()
	{
		return $this->dateEnd;
	}

	/**
	 * @param \DateTime $dateStart
	 */
	public function setDateStart($dateStart)
	{
		$this->dateStart = $dateStart;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateStart()
	{
		return $this->dateStart;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return \ITILSimulator\Entities\Training\Scenario
	 */
	public function getScenario()
	{
		return $this->scenario;
	}

	/**
	 * @return \ITILSimulator\Entities\Session\Session
	 */
	public function getSession()
	{
		return $this->session;
	}

	/**
	 * @param \ITILSimulator\Entities\Training\Scenario $scenario
	 */
	public function setScenario($scenario)
	{
		$this->scenario = $scenario;
	}

	/**
	 * @param \ITILSimulator\Entities\Session\Session $session
	 */
	public function setSession($session)
	{
		$this->session = $session;
	}

	/**
	 * @return bool
	 */
	public function getIsFinished()
	{
		return $this->isFinished;
	}

	/**
	 * @return bool
	 */
	public function isFinished()
	{
		return $this->getIsFinished();
	}

	/**
	 * @return ArrayCollection|ScenarioStep[]
	 */
	public function getScenarioSteps()
	{
		return $this->scenarioSteps;
	}

	/**
	 * @param float $budget
	 */
	public function setBudget($budget)
	{
		$this->budget = $budget;
	}

	/**
	 * @return float
	 */
	public function getBudget()
	{
		return $this->budget;
	}

	/**
	 * @param int $evaluationPoints
	 */
	public function setEvaluationPoints($evaluationPoints)
	{
		$this->evaluationPoints = $evaluationPoints;
	}

	/**
	 * @return int
	 */
	public function getEvaluationPoints()
	{
		return $this->evaluationPoints;
	}

	#endregion

	public function __toString() {
		return (string)$this->getId();
	}
}