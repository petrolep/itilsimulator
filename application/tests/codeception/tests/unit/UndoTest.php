<?php
/**
 * UndoTest.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 21.5.13 16:18
 */

class UndoTest extends \Codeception\TestCase\Test {
	const TRAINING_STEP_ID = 1;

	/** @var \ITILSimulator\Services\SessionService */
	protected $service;

	/** @var \ITILSimulator\Services\ArtifactService */
	protected $artifactsService;

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

		$artifactService = new \ITILSimulator\Services\ArtifactService();
		$artifactService->setEventsRepository($factory->createOperationEventRepository($em));
		$artifactService->setIncidentsRepository($factory->createOperationIncidentRepository($em));
		$artifactService->setProblemsRepository($factory->createOperationProblemRepository($em));
		$this->artifactsService = $artifactService;
	}

	public function testUndo() {
		$trainingStep = $this->service->getTrainingStep(self::TRAINING_STEP_ID);
		$this->assertNotNull($trainingStep);

		$validScenarioStep = $trainingStep->getLastValidScenarioStep();

		$events = $this->artifactsService->getAvailableEvents($validScenarioStep->getId());
		$this->assertFalse($events[0]->getIsUndid());

		// undo
		$this->assertFalse($validScenarioStep->isUndid());

		$this->service->undoScenarioStep($validScenarioStep);
		$this->artifactsService->undoScenarioStep($validScenarioStep);
		$this->codeGuy->flushToDatabase();

		$trainingStep = $this->service->getTrainingStep(self::TRAINING_STEP_ID); // reload training step
		$previousScenarioStep = $trainingStep->getLastValidScenarioStep();
		$this->assertNotEquals($validScenarioStep->getId(), $previousScenarioStep->getId());
		$this->assertTrue($validScenarioStep->isUndid());

		// reload event -- should return nothing as the event was undid
		$event = $this->artifactsService->getEvent($events[0]->getOriginalId());
		$this->assertNull($event);

	}
}
