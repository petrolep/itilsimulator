<?php
/**
 * SessionTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 21.5.13 15:26
 */

class SessionTest extends \Codeception\TestCase\Test {
	const ADMIN_USER_ID = 1;
	const TRAINING_STEP_ID = 1;

	/** @var \ITILSimulator\Services\SessionService */
	protected $service;

	/** @var \ITILSimulator\Services\TrainingService */
	protected $trainingService;

	/** @var \ITILSimulator\Base\ITILConfigurator */
	protected $config;

	public function setUp() {
		parent::setUp();

		$em = \Codeception\Module\Doctrine2::$em;

		$factory = new \ITILSimulator\Base\DoctrineFactory();
		$this->config = new \ITILSimulator\Base\ITILConfigurator(array());

		$service = new \ITILSimulator\Services\SessionService();
		$service->setSessionRepository($factory->createSessionRepository($em));
		$service->setTrainingStepRepository($factory->createTrainingStepRepository($em));
		$this->service = $service;

		$trainingService = new \ITILSimulator\Services\TrainingService();
		$service->setTrainingService($trainingService);
		$trainingService->setTrainingRepository($factory->createTrainingRepository($em));
		$this->trainingService = $trainingService;
	}

	public function testNewSession() {
		// test data must be loaded into database!
		$sessions = $this->service->getUserActiveSessions(self::ADMIN_USER_ID);
		$this->assertEquals(1, count($sessions));

		// create a new session
		$session = $sessions[0];
		$this->assertEquals($session, $this->service->getSession($session->getId()));

		$trainings = $this->trainingService->getTrainingsByUser($session->getUser());
		$newSession = $this->service->startNewSession($session->getUser(), $trainings[0]);
		$this->codeGuy->flushToDatabase();

		$sessions = $this->service->getUserActiveSessions(self::ADMIN_USER_ID);
		$this->assertEquals(2, count($sessions));

		// finish session
		$this->assertFalse($newSession->isFinished());

		$this->service->finishSession($newSession);
		$this->codeGuy->flushToDatabase();

		$newSession = $this->service->getSession($newSession->getId());
		$this->assertTrue($newSession->isFinished());

		// delete session
		$this->service->deleteSession($newSession);
		$this->codeGuy->flushToDatabase();

		$sessions = $this->service->getUserActiveSessions(self::ADMIN_USER_ID);
		$this->assertEquals(1, count($sessions));
	}

	public function testEvaluation() {
		$trainingStep = $this->service->getTrainingStep(self::TRAINING_STEP_ID);
		$this->assertNotNull($trainingStep);

		$validScenarioStep = $trainingStep->getLastValidScenarioStep();

		// change evaluation
		$originalBudget = $validScenarioStep->getBudget();
		$originalPoints = $validScenarioStep->getEvaluationPoints();

		$this->service->addEvaluationPoints($validScenarioStep, 10, 15.50);
		$this->codeGuy->flushToDatabase();

		$trainingStep = $this->service->getTrainingStep(1); // reload training step
		$newValidScenarioStep = $trainingStep->getLastValidScenarioStep();
		$this->assertEquals($originalBudget + 15.50, $newValidScenarioStep->getBudget());
		$this->assertEquals($originalPoints + 10, $newValidScenarioStep->getEvaluationPoints());
	}
}
