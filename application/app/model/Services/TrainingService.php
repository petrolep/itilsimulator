<?php
/**
 * TrainingService.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 7.4.13 15:30
 */

namespace ITILSimulator\Services;

use ITILSimulator\Entities\Simulator\User;
use ITILSimulator\Entities\Training\ConfigurationItem;
use ITILSimulator\Entities\Training\ConfigurationItemSpecification;
use ITILSimulator\Entities\Training\InputOutput;
use ITILSimulator\Entities\Training\KnownIssue;
use ITILSimulator\Entities\Training\Scenario;
use ITILSimulator\Entities\Training\Service;
use ITILSimulator\Entities\Training\ServiceSpecification;
use ITILSimulator\Entities\Training\Training;
use ITILSimulator\Repositories\Training\ConfigurationItemRepository;
use ITILSimulator\Repositories\Training\ConfigurationItemSpecificationRepository;
use ITILSimulator\Repositories\Training\InputOutputRepository;
use ITILSimulator\Repositories\Training\KnownIssueRepository;
use ITILSimulator\Repositories\Training\ScenarioRepository;
use ITILSimulator\Repositories\Training\ServiceRepository;
use ITILSimulator\Repositories\Training\TrainingRepository;
use ITILSimulator\Runtime\Simulator\EntityFilter;
use ITILSimulator\Runtime\Simulator\Monitoring\ConfigurationItemAttributeHistory;
use Nette\InvalidArgumentException;

/**
 * Training services. Handles Trainings and Scenarios.
 * @package ITILSimulator\Services
 */
class TrainingService implements ITransactionService
{
	#region "Properties"

	/** @var TrainingRepository */
	protected $trainingRepository;

	/** @var ServiceRepository */
	protected $serviceRepository;

	/** @var ConfigurationItemRepository */
	protected $configurationItemRepository;

	/** @var ScenarioRepository */
	protected $scenarioRepository;

	/** @var ConfigurationItemSpecificationRepository */
	protected $configurationItemSpecificationRepository;

	/** @var InputOutputRepository */
	protected $inputOutputRepository;

	protected $knownIssueRepository;

	#endregion

	#region "Dependencies"

	/**
	 * @param \ITILSimulator\Repositories\Training\ConfigurationItemRepository $configurationItemRepository
	 */
	public function setConfigurationItemRepository(ConfigurationItemRepository $configurationItemRepository)
	{
		$this->configurationItemRepository = $configurationItemRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Training\ConfigurationItemRepository
	 */
	public function getConfigurationItemRepository()
	{
		return $this->configurationItemRepository;
	}

	/**
	 * @param \ITILSimulator\Repositories\Training\ConfigurationItemSpecificationRepository $configurationItemSpecificationRepository
	 */
	public function setConfigurationItemSpecificationRepository(ConfigurationItemSpecificationRepository $configurationItemSpecificationRepository)
	{
		$this->configurationItemSpecificationRepository = $configurationItemSpecificationRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Training\ConfigurationItemSpecificationRepository
	 */
	public function getConfigurationItemSpecificationRepository()
	{
		return $this->configurationItemSpecificationRepository;
	}

	/**
	 * @param \ITILSimulator\Repositories\Training\InputOutputRepository $inputOutputRepository
	 */
	public function setInputOutputRepository(InputOutputRepository $inputOutputRepository)
	{
		$this->inputOutputRepository = $inputOutputRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Training\InputOutputRepository
	 */
	public function getInputOutputRepository()
	{
		return $this->inputOutputRepository;
	}

	/**
	 * @param \ITILSimulator\Repositories\Training\KnownIssueRepository $knownIssueRepository
	 */
	public function setKnownIssueRepository(KnownIssueRepository $knownIssueRepository)
	{
		$this->knownIssueRepository = $knownIssueRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Training\KnownIssueRepository
	 */
	public function getKnownIssueRepository()
	{
		return $this->knownIssueRepository;
	}

	/**
	 * @param \ITILSimulator\Repositories\Training\ScenarioRepository $scenarioRepository
	 */
	public function setScenarioRepository(ScenarioRepository $scenarioRepository)
	{
		$this->scenarioRepository = $scenarioRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Training\ScenarioRepository
	 */
	public function getScenarioRepository()
	{
		return $this->scenarioRepository;
	}

	/**
	 * @param \ITILSimulator\Repositories\Training\ServiceRepository $serviceRepository
	 */
	public function setServiceRepository(ServiceRepository $serviceRepository)
	{
		$this->serviceRepository = $serviceRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Training\ServiceRepository
	 */
	public function getServiceRepository()
	{
		return $this->serviceRepository;
	}

	/**
	 * @param \ITILSimulator\Repositories\Training\TrainingRepository $trainingRepository
	 */
	public function setTrainingRepository(TrainingRepository $trainingRepository)
	{
		$this->trainingRepository = $trainingRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Training\TrainingRepository
	 */
	public function getTrainingRepository()
	{
		return $this->trainingRepository;
	}

	#endregion

	#region "Public API"

	#region "Trainings"

	/**
	 * Return training
	 * @param int $trainingId
	 * @return Training
	 */
	public function getTraining($trainingId) {
		return $this->trainingRepository->findOneBy(array('id' => $trainingId));
	}

	/**
	 * Update training
	 * @param Training $training
	 * @return Training
	 */
	public function updateTraining(Training $training)
	{
		$this->trainingRepository->save($training);

		return $training;
	}

	/**
	 * Return trainings created by specified user
	 * @param User $user
	 * @return Training[]
	 */
	public function getTrainingsByUser(User $user)
	{
		return $this->trainingRepository->findAllByUser($user->getId());
	}

	/**
	 * Return training and check if it was created by specified user
	 * @param User $user
	 * @param int $trainingId
	 * @return Training|null
	 */
	public function getTrainingByUser(User $user, $trainingId)
	{
		/** @var $training Training */
		$training = $this->trainingRepository->findOneBy(array('id' => $trainingId));
		if ($training && $training->getUser() && $training->getUser()->getId() == $user->getId())
			return $training;

		return null;
	}

	/**
	 * Return published trainings
	 * @param bool $onlyPublic TRUE if only public trainings should be returned.
	 * @return Training[]
	 */
	public function getPublishedTrainings($onlyPublic = false) {
		return $this->trainingRepository->findAllPublished($onlyPublic);
	}

	/**
	 * Publish training
	 * @param Training $training
	 * @param bool $isPublished
	 */
	public function publishTraining(Training $training, $isPublished) {
		$training->setIsPublished($isPublished);

		$this->updateTraining($training);
	}

	/**
	 * Make training public/private
	 * @param Training $training
	 * @param bool $isPublic TRUE to make it public, FALSE to private.
	 */
	public function publicizeTraining(Training $training, $isPublic) {
		$training->setIsPublic($isPublic);
		$this->updateTraining($training);
	}

	/**
	 * Delete training
	 * @param Training $training
	 */
	public function deleteTraining(Training $training) {
		$this->trainingRepository->remove($training);
	}

	#endregion

	#region "Scenarios"

	/**
	 * Return scenario
	 * @param int $scenarioId
	 * @return Scenario
	 */
	public function getScenario($scenarioId) {
		$scenario = $this->scenarioRepository->findOneBy(array('id' => $scenarioId));

		return $scenario;
	}

	/**
	 * Update scenario
	 * @param Scenario $scenario
	 */
	public function updateScenario(Scenario $scenario) {
		$this->scenarioRepository->save($scenario);
	}

	/**
	 * Delete scenario
	 * @param Scenario $scenario
	 */
	public function deleteScenario(Scenario $scenario) {
		$this->scenarioRepository->remove($scenario);
	}

	#endregion

	#region "Services"

	/**
	 * Return service
	 * @param int $serviceId
	 * @return Service
	 */
	public function getService($serviceId)
	{
		/** @var $service Service */
		$service = $this->serviceRepository->findOneBy(array('id' => $serviceId));

		return $service;
	}

	/**
	 * Create new service
	 * @param Training $training
	 * @param Service $service
	 */
	public function createService(Training $training, Service $service)
	{
		$service->setTraining($training);
		$this->trainingRepository->save($service);
	}

	/**
	 * Update existing service
	 * @param Service $service
	 * @throws \Nette\InvalidArgumentException
	 */
	public function updateService(Service $service) {
		if (!$service->getId())
			throw new InvalidArgumentException('Service must be persisted. User "createService" to create a new service.');

		$this->trainingRepository->save($service);
	}

	/**
	 * Create default specification for service
	 * @param ServiceSpecification $serviceSpecification
	 */
	public function createServiceDefaultSpecification(ServiceSpecification $serviceSpecification) {
		$serviceSpecification->setIsDefault(true);
		$this->serviceRepository->save($serviceSpecification);
	}

	/**
	 * Update service specification
	 * @param ServiceSpecification $serviceSpecification
	 * @throws \Nette\InvalidArgumentException
	 */
	public function updateServiceSpecification(ServiceSpecification $serviceSpecification) {
		if (!$serviceSpecification->getId())
			throw new InvalidArgumentException('Specification must be persisted. Use "createServiceDefaultSpecification" to create a new specification.');

		$this->serviceRepository->save($serviceSpecification);
	}

	#endregion

	#region "Configuration items"

	/**
	 * Return configuration item
	 * @param $configurationItemId
	 * @return ConfigurationItem
	 */
	public function getConfigurationItem($configurationItemId) {
		/** @var $configurationItem ConfigurationItem */
		$configurationItem = $this->configurationItemRepository->findOneBy(array('id' => $configurationItemId));

		return $configurationItem;
	}

	/**
	 * Return all configuration items assigned to given service
	 * @param int $serviceId
	 * @param bool $allowGlobal TRUE to return also all global configuration items, FALSE to return only CI from the service.
	 * @return ConfigurationItem[]
	 */
	public function getConfigurationItemsByService($serviceId, $allowGlobal = false) {
		$configurationItems = $this->configurationItemRepository->findAllByService($serviceId, $allowGlobal);

		return $configurationItems;
	}

	/**
	 * Update configuration item
	 * @param ConfigurationItem $ci
	 */
	public function updateConfigurationItem(ConfigurationItem $ci)
	{
		$this->configurationItemRepository->save($ci);
	}

	/**
	 * Update default specification of a configuration item
	 * @param ConfigurationItemSpecification $ciSpecification
	 */
	public function updateConfigurationItemDefaultSpecification(ConfigurationItemSpecification $ciSpecification) {
		$ciSpecification->setIsDefault(true);
		$this->configurationItemSpecificationRepository->save($ciSpecification);
	}

	/**
	 * Delete configuration item
	 * @param ConfigurationItem $ci
	 */
	public function deleteConfigurationItem(ConfigurationItem $ci)
	{
		$this->configurationItemRepository->remove($ci);
	}

	/**
	 * Return history of all changes of custom attributes in a configuration item
	 * @param $configurationItemId
	 * @param $trainingStepId
	 * @return ConfigurationItemAttributeHistory[]
	 */
	public function getConfigurationItemAttributesHistory($configurationItemId, $trainingStepId) {
		$data = $this->configurationItemSpecificationRepository->getAttributesHistory($configurationItemId, $trainingStepId);
		$result = array();
		foreach ($data as $row) {
			$result[] = new ConfigurationItemAttributeHistory($row);
		}

		return $result;
	}

	#endregion

	#region "Input & output"

	/**
	 * Return available Input/Output for training
	 * @param int $trainingId
	 * @return InputOutput[]
	 */
	public function getAvailableInputsOutputs($trainingId) {
		return $this->inputOutputRepository->findBy(array('training' => $trainingId), array('name' => 'ASC'));
	}

	/**
	 * Return Input/Output
	 * @param int $id
	 * @return InputOutput
	 */
	public function getInputOutput($id) {
		return $this->inputOutputRepository->findOneBy(array('id' => $id));
	}

	/**
	 * Update Input/Output
	 * @param InputOutput $inputOutput
	 */
	public function updateInputOutput(InputOutput $inputOutput) {
		$this->inputOutputRepository->save($inputOutput);
	}

	/**
	 * Delete Input/Output
	 * @param InputOutput $inputOutput
	 */
	public function deleteInputOutput(InputOutput $inputOutput) {
		$this->inputOutputRepository->remove($inputOutput);
	}

	#endregion

	#region "Known issues"

	/**
	 * Return available known error (issue)
	 * @param int $trainingId
	 * @param EntityFilter|null $filter
	 * @return KnownIssue[]
	 */
	public function getAvailableKnownIssues($trainingId, $filter = null) {
		return $this->knownIssueRepository->getAvailable($trainingId, $filter);
	}

	/**
	 * Return known error (issue)
	 * @param int $id
	 * @return KnownIssue
	 */
	public function getKnownIssue($id) {
		return $this->knownIssueRepository->findOneBy(array('id' => $id));
	}

	/**
	 * Return known error (issue) by code
	 * @param Training $training
	 * @param string $code
	 * @return KnownIssue
	 */
	public function getKnownIssueByCode(Training $training, $code) {
		return $this->knownIssueRepository->findOneBy(array('code' => $code, 'training' => $training));
	}

	/**
	 * Update known error (issue)
	 * @param KnownIssue $knownIssue
	 */
	public function updateKnownIssue(KnownIssue $knownIssue) {
		$this->knownIssueRepository->save($knownIssue);
	}

	/**
	 * Delete known error (issue)
	 * @param KnownIssue $knownIssue
	 */
	public function deleteKnownIssue(KnownIssue $knownIssue) {
		$this->knownIssueRepository->remove($knownIssue);
	}

	#endregion

	/**
	 * Commit changes to database
	 */
	public function commitChanges() {
		$this->scenarioRepository->commit();
	}

	#endregion
}