<?php
/**
 * ServicePresenter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 7.4.13 19:43
 */

namespace CreatorModule;

use ITILSimulator\Base\TemplateHelpers;
use ITILSimulator\Creator\Presenters\CreatorPresenter;
use ITILSimulator\Entities\Training\Service;
use ITILSimulator\Entities\Training\ServiceSpecification;
use ITILSimulator\Entities\Training\Training;
use ITILSimulator\Runtime\Training\CustomServiceAttribute;
use ITILSimulator\Runtime\Training\PriorityEnum;
use ITILSimulator\Services\TrainingService;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

/**
 * Presenter for managing services.
 * @package CreatorModule
 */
class ServicePresenter extends CreatorPresenter
{
	/**
	 * Allowed number of custom attributes in service
	 */
	const CUSTOM_ATTRIBUTES_COUNT = 10;

	#region "Properties"

	/** @var TrainingService */
	protected $trainingService;

	/** @var Training */
	protected $selectedTraining = null;

	/** @var Service */
	protected $selectedService = null;

	/** @var array Values to be stored in Service object */
	private $serviceItemValues = array('name', 'code', 'serviceOwner', 'description', 'graphicDesignData');

	/** @var array Values to be stored in ServiceSpecification object */
	private $specificationValues = array('priority', 'earnings');

	#endregion

	#region "Lifecycle methods"

	/**
	 * @param TrainingService $trainingService
	 */
	public function inject(TrainingService $trainingService) {
		$this->trainingService = $trainingService;
	}

	#endregion

	#region "New service"

	/**
	 * Create new service
	 * @param int $trainingId Training ID
	 */
	public function actionNew($trainingId) {
		$this->selectedTraining = $this->loadTraining($trainingId);
		$this->template->training = $this->selectedTraining;
		$this->template->attributesCount = self::CUSTOM_ATTRIBUTES_COUNT;

		if ($this->isAjax()) {
			$this->setLayout('empty');
		}
	}

	/**
	 * Display detail of existing service
	 * @param int $id Service ID
	 * @throws \Nette\Application\BadRequestException
	 */
	public function actionDetail($id) {
		$service = $this->trainingService->getService($id);
		if (!$service || !$service->getTraining() || $service->getUserId() != $this->getUserId()) {
			throw new BadRequestException('Invalid service ID.');
		}

		$this->selectedService = $this->template->service = $service;
		$this->selectedTraining = $this->template->selectedTraining = $service->getTraining();

		if (!$this['serviceForm']->isSubmitted()) {
			if ($this->getParameter('cancel')) {
				$this->invalidateControl('serviceForm');

			} else {
				$this->populateServiceForm($service);
			}

		} else {
			$this->template->showEdit = true;
			$this->invalidateControl('serviceForm');
		}
	}

	#endregion

	#region "Edit service"

	/**
	 * Edit an existing service
	 * @param int $id Service ID
	 */
	public function handleEditService($id) {
		$this->template->showEdit = true;
		$this->template->attributesCount = self::CUSTOM_ATTRIBUTES_COUNT;
		$this->invalidateControl('serviceForm');
	}

	#endregion

	#region "AJAX requests"

	/**
	 * AJAX request to refresh configuration items list
	 */
	public function handleRefreshConfigurationItems() {
		$this->invalidateControl('configurationItems');
	}

	#endregion

	#region "Helpers"

	/**
	 * Load training
	 * @param int $trainingId
	 * @return Training|null
	 * @throws \Nette\Application\BadRequestException
	 */
	protected function loadTraining($trainingId)
	{
		$training = $this->trainingService->getTrainingByUser($this->getUserIdentity(), $trainingId);
		if (!$training) {
			throw new BadRequestException('Training not found.');
		}

		return $training;
	}

	/**
	 * Populate service form
	 * @param $service
	 */
	protected function populateServiceForm(Service $service)
	{
		$defaultValues = array();
		foreach ($this->serviceItemValues as $value) {
			$defaultValues[$value] = $service->$value;
		}

		$defaultSpecification = $service->getDefaultSpecification();
		foreach ($this->specificationValues as $value) {
			$defaultValues[$value] = $defaultSpecification->$value;
		}

		$i = 0;
		foreach($defaultSpecification->getAttributes() as $attribute) {
			$defaultValues['attribute_name_' . $i] = $attribute->getName();
			$defaultValues['attribute_code_' . $i] = $attribute->getCode();
			$defaultValues['attribute_value_' . $i] = $attribute->getCurrentValue();
			$defaultValues['attribute_minimum_' . $i] = $attribute->getMinimumValue();
			$defaultValues['attribute_maximum_' . $i] = $attribute->getMaximumValue();
			$defaultValues['attribute_unit_' . $i] = $attribute->getUnit();
			$i++;
		}

		$this['serviceForm']->setDefaults($defaultValues);
	}

	#endregion

	#region "Components"

	/**
	 * Create service form component
	 * @return Form
	 */
	protected function createComponentServiceForm() {
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->addText('name', 'Service name:')
			->addRule(Form::FILLED, 'Service name is required.')
			->getLabelPrototype()->addClass('required');

		$form->addText('code', 'ID:')
			->getLabelPrototype()->setTitle($this->translator->translate('Custom unique ID to access the service.'));

		$form->addSelect('priority', 'Priority:', PriorityEnum::getOptions());
		$form->addTextArea('description', 'Description:', 40, 4);
		$form->addText('serviceOwner', 'Service owner:');
		$form->addText('earnings', 'Earnings (' . TemplateHelpers::emptyCurrency() . '):')
			->setType('number')
			->setDefaultValue(0)
			->setAttribute('min', 0)
			->setAttribute('max', 1000000)
			->addCondition(Form::FILLED)
				->addRule(Form::FLOAT, 'Earnings must be a valid number.')
				->addRule(Form::RANGE, 'Earnings must be between %d and %d', array(0, 1000000));

		$form->addTextArea('graphicDesignData', 'Graphic design data (HTML + CSS):');

		for($i = 0; $i < self::CUSTOM_ATTRIBUTES_COUNT; $i++) {
			$form->addText('attribute_name_' . $i, 'Name:');
			$form->addText('attribute_code_' . $i, 'ID:');
			$form->addText('attribute_value_' . $i, 'Default value:');
			$form->addText('attribute_minimum_' . $i, 'Min value:');
			$form->addText('attribute_maximum_' . $i, 'Max value:');
			$form->addText('attribute_unit_' . $i, 'Unit:');
		}

		$form->addSubmit('save', 'Save service');

		$form->onSuccess[] = $this->onServiceFormSuccess;

		return $form;
	}

	/**
	 * Save service form
	 * @param Form $form
	 */
	public function onServiceFormSuccess(Form $form) {
		$values = $form->getValues();

		$isNew = !$this->selectedService;
		$service = $isNew ? new Service() : $this->selectedService;

		$specification = $service->getDefaultSpecification();
		if (!$specification)
			$specification = new ServiceSpecification($service);

		foreach ($this->serviceItemValues as $key) {
			$service->$key = $values[$key];
		}

		foreach ($this->specificationValues as $key) {
			$specification->$key = $values[$key];
		}

		// custom attributes
		$customAttributes = array();
		for($i = 0; $i < self::CUSTOM_ATTRIBUTES_COUNT; $i++) {
			if ($values['attribute_name_' . $i] && $values['attribute_value_' . $i] && $values['attribute_code_' . $i]) {
				$customAttributes[$values['attribute_code_' . $i]] = new CustomServiceAttribute($values['attribute_name_' . $i],
																				$values['attribute_code_' . $i],
																				$values['attribute_value_' . $i],
																				$values['attribute_minimum_' . $i],
																				$values['attribute_maximum_' . $i],
																				$values['attribute_unit_' . $i]);
			}
		}
		$specification->setAttributes($customAttributes);

		if ($isNew) {
			$this->trainingService->createService($this->selectedTraining, $service);
			$this->trainingService->createServiceDefaultSpecification($specification);

		} else {
			$this->trainingService->updateService($service);
			$this->trainingService->updateServiceSpecification($specification);
		}

		$this->flashInfoMessage('Service %s was saved.', $service->getName());

		$this->trainingService->commitChanges();

		if ($this->isAjax()) {
			$this->template->showEdit = false;
			$this->invalidateControl('serviceForm');

		} else {
			$this->redirect('service:detail', array('id' => $service->getId()));
		}
	}

	#endregion

}