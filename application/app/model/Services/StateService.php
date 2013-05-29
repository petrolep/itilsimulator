<?php
/**
 * StateService.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 13.4.13 21:58
 */

namespace ITILSimulator\Services;


use ITILSimulator\Entities\Training\ConfigurationItemSpecification;
use ITILSimulator\Entities\Training\ServiceSpecification;
use ITILSimulator\Entities\Workflow\WorkflowActivitySpecification;
use ITILSimulator\Repositories\Training\ConfigurationItemSpecificationRepository;
use ITILSimulator\Repositories\Training\ServiceSpecificationRepository;
use ITILSimulator\Repositories\Workflow\WorkflowActivitySpecificationRepository;

/**
 * State service. Handles states of workflow activities, configuration items and services.
 * @package ITILSimulator\Services
 */
class StateService implements ITransactionService
{
	#region "Properties"

	/** @var ServiceSpecificationRepository */
	protected $serviceSpecificationRepository;

	/** @var ConfigurationItemSpecificationRepository */
	protected $configurationItemSpecificationRepository;

	/** @var WorkflowActivitySpecificationRepository */
	protected $workflowActivitySpecificationRepository;

	#endregion

	#region "Constructor"

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
	 * @param \ITILSimulator\Repositories\Training\ServiceSpecificationRepository $serviceSpecificationRepository
	 */
	public function setServiceSpecificationRepository(ServiceSpecificationRepository $serviceSpecificationRepository)
	{
		$this->serviceSpecificationRepository = $serviceSpecificationRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Training\ServiceSpecificationRepository
	 */
	public function getServiceSpecificationRepository()
	{
		return $this->serviceSpecificationRepository;
	}

	/**
	 * @param \ITILSimulator\Repositories\Workflow\WorkflowActivitySpecificationRepository $workflowActivitySpecificationRepository
	 */
	public function setWorkflowActivitySpecificationRepository(WorkflowActivitySpecificationRepository $workflowActivitySpecificationRepository)
	{
		$this->workflowActivitySpecificationRepository = $workflowActivitySpecificationRepository;
	}

	/**
	 * @return \ITILSimulator\Repositories\Workflow\WorkflowActivitySpecificationRepository
	 */
	public function getWorkflowActivitySpecificationRepository()
	{
		return $this->workflowActivitySpecificationRepository;
	}



	#endregion

	#region "Public API"

	/**
	 * Return specification of a service
	 * @param int $id
	 * @return ServiceSpecification
	 */
	public function getServiceSpecification($id)
	{
		/** @var $specification ServiceSpecification */
		$specification = $this->serviceSpecificationRepository->findOneBy(array('id' => $id));

		return $specification;
	}

	/**
	 * Return specification of a configuration item
	 * @param int $id
	 * @return ConfigurationItemSpecification
	 */
	public function getConfigurationItemSpecification($id)
	{
		/** @var $specification ConfigurationItemSpecification */
		$specification = $this->configurationItemSpecificationRepository->findOneBy(array('id' => $id));

		return $specification;
	}

	/**
	 * Return specification of a workflow activity
	 * @param int $id
	 * @return WorkflowActivitySpecification
	 */
	public function getWorkflowActivitySpecification($id)
	{
		/** @var $specification WorkflowActivitySpecification */
		$specification = $this->workflowActivitySpecificationRepository->findOneBy(array('id' => $id));

		return $specification;
	}

	/**
	 * Return default specification for configuration item
	 * @param int $id
	 * @return ConfigurationItemSpecification
	 */
	public function getConfigurationItemDefaultSpecification($id) {
		/** @var $specification ConfigurationItemSpecification */
		$specification = $this->configurationItemSpecificationRepository->findOneBy(array('isDefault' => true, 'configurationItem' => $id));

		return $specification;
	}

	/**
	 * Return default specification for workflow activity
	 * @param int $id
	 * @return WorkflowActivitySpecification
	 */
	public function getWorkflowActivityDefaultSpecification($id) {
		/** @var $specification WorkflowActivitySpecification */
		$specification = $this->workflowActivitySpecificationRepository->findOneBy(array('isDefault' => true, 'workflowActivity' => $id));

		return $specification;
	}

	/**
	 * Return default specification for service
	 * @param int $id
	 * @return ServiceSpecification
	 */
	public function getServiceDefaultSpecification($id) {
		/** @var $specification ServiceSpecification */
		$specification = $this->serviceSpecificationRepository->findOneBy(array('isDefault' => true, 'service' => $id));

		return $specification;
	}

	/**
	 * Commit changes to database
	 */
	public function commitChanges() {
		$this->serviceSpecificationRepository->commit();
	}

	#endregion
}