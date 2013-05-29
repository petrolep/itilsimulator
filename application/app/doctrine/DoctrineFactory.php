<?php
/**
 * DoctrineFactory.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 1.5.13 21:57
 */

namespace ITILSimulator\Base;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use ITILSimulator;
use Nella\Addons;
use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;

/**
 * Factory to construct Doctrine environment and establish database connection
 * @package ITILSimulator\Base
 */
class DoctrineFactory
{
	protected $configuration;

	/**
	 * Create Entity manager
	 * @param $configuration
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function createEntityManager($configuration)
	{
		$isDevMode = true;
		$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__ . "/../model/Entities"), $isDevMode);
		$config->setSQLLogger(Addons\Doctrine\Diagnostics\ConnectionPanel::register());

		$config->setProxyDir(__DIR__ . "/../model/Proxies");
		$config->setProxyNamespace('Proxies');

		// Auto generate proxies for development
		$config->setAutoGenerateProxyClasses($isDevMode);

		$cacheDir = __DIR__ . '/../../temp/cache/_Doctrine';
		if (!file_exists($cacheDir) || !is_dir($cacheDir))
			mkdir($cacheDir);

		$cacheStorage = new FileStorage($cacheDir);
		$cache = new CustomCacheProvider(new Cache($cacheStorage));
		$config->setQueryCacheImpl($cache);
		$config->setMetadataCacheImpl($cache);

		// obtaining the entity manager
		$entityManager = EntityManager::create($configuration, $config);
		$entityManager->getEventManager()->addEventSubscriber(
			new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit('utf8', 'utf8_unicode_ci')
		);

		$this->configuration = $configuration;

		return $entityManager;
	}

	/**
	 * Return loaded configuration
	 * @return mixed
	 */
	public function getConfiguration() {
		return $this->configuration;
	}

	/**
	 * @param EntityManager $em
	 * @return ITILSimulator\Repositories\OperationArtifact\OperationEventRepository
	 */
	public function createOperationEventRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\OperationArtifact\OperationEvent');
	}

	/**
	 * @param EntityManager $em
	 * @return ITILSimulator\Repositories\OperationArtifact\OperationIncidentRepository
	 */
	public function createOperationIncidentRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\OperationArtifact\OperationIncident');
	}

	/**
	 * @param EntityManager $em
	 * @return ITILSimulator\Repositories\OperationArtifact\OperationProblemRepository
	 */
	public function createOperationProblemRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\OperationArtifact\OperationProblem');
	}

	/**
	 * @param EntityManager $em
	 * @return ITILSimulator\Repositories\OperationArtifact\OperationCategoryRepository
	 */
	public function createOperationCategoryRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\OperationArtifact\OperationCategory');
	}

	/**
	 * @param EntityManager $em
	 * @return ITILSimulator\Repositories\Workflow\WorkflowRepository
	 */
	public function createWorkflowRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\Workflow\Workflow');
	}

	/**
	 * @param EntityManager $em
	 * @return ITILSimulator\Repositories\Workflow\WorkflowActivityRepository
	 */
	public function createWorkflowActivityRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\Workflow\WorkflowActivity');
	}

	/**
	 * @param EntityManager $em
	 * @return ITILSimulator\Repositories\Workflow\WorkflowActivitySpecificationRepository
	 */
	public function createWorkflowActivitySpecificationRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\Workflow\WorkflowActivitySpecification');
	}

	/**
	 * @param EntityManager $em
	 * @return ITILSimulator\Repositories\Training\TrainingRepository
	 */
	public function createTrainingRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\Training\Training');
	}

	/**
	 * @param EntityManager $em
	 * @return ITILSimulator\Repositories\Session\TrainingStepRepository
	 */
	public function createTrainingStepRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\Session\TrainingStep');
	}

	/**
	 * @param EntityManager $em
	 * @return ITILSimulator\Repositories\Training\ServiceRepository
	 */
	public function createServiceRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\Training\Service');
	}

	/**
	 * @param EntityManager $em
	 * @return ITILSimulator\Repositories\Training\ConfigurationItemRepository
	 */
	public function createConfigurationItemRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\Training\ConfigurationItem');
	}

	/**
	 * @param EntityManager $em
	 * @return ITILSimulator\Repositories\Training\ScenarioRepository
	 */
	public function createScenarioRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\Training\Scenario');
	}

	/**
	 * @param EntityManager $em
	 * @return ITILSimulator\Repositories\Training\ConfigurationItemSpecificationRepository
	 */
	public function createConfigurationItemSpecificationRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\Training\ConfigurationItemSpecification');
	}

	/**
	 * @param EntityManager $em
	 * @return ITILSimulator\Repositories\Training\InputOutputRepository
	 */
	public function createInputOutputRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\Training\InputOutput');
	}

	/**
	 * @param EntityManager $em
	 * @return ITILSimulator\Repositories\Training\KnownIssueRepository
	 */
	public function createKnownIssueRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\Training\KnownIssue');
	}

	/**
	 * @param EntityManager $em
	 * @return \ITILSimulator\Repositories\Simulator\UserRepository
	 */
	public function createUserRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\Simulator\User');
	}

	/**
	 * @param EntityManager $em
	 * @return \ITILSimulator\Repositories\Simulator\RoleRepository
	 */
	public function createRoleRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\Simulator\Role');
	}

	/**
	 * @param EntityManager $em
	 * @return \ITILSimulator\Repositories\Training\ScenarioDesignResultRepository
	 */
	public function createScenarioDesignResultRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\Training\ScenarioDesignResult');
	}

	/**
	 * @param EntityManager $em
	 * @return \ITILSimulator\Repositories\Session\SessionRepository
	 */
	public function createSessionRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\Session\Session');
	}

	/**
	 * @param EntityManager $em
	 * @return \ITILSimulator\Repositories\Training\ServiceSpecificationRepository
	 */
	public function createServiceSpecificationRepository(EntityManager $em) {
		return $em->getRepository('ITILSimulator\Entities\Training\ServiceSpecification');
	}
}