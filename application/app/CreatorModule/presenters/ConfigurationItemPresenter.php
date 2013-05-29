<?php
/**
 * ConfigurationItemPresenter.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 7.4.13 21:33
 */

namespace CreatorModule;

use ITILSimulator\Base\JavaScriptTranslator\JavaScriptTranslator;
use ITILSimulator\Base\JavaScriptTranslator\PHPValidator;
use ITILSimulator\Base\TemplateHelpers;
use ITILSimulator\Creator\Presenters\CreatorPresenter;
use ITILSimulator\Entities\Training\ConfigurationItem;
use ITILSimulator\Entities\Training\ConfigurationItemSpecification;
use ITILSimulator\Entities\Training\Service;
use ITILSimulator\Runtime\Training\CustomServiceAttribute;
use ITILSimulator\Runtime\Training\PriorityEnum;
use ITILSimulator\Services\TrainingService;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

/**
 * Presenter for managing configuration items.
 * @package CreatorModule
 */
class ConfigurationItemPresenter extends CreatorPresenter
{
	/**
	 * Allowed number of custom attributes in configuration item
	 */
	const CUSTOM_ATTRIBUTES_COUNT = 10;

	#region "Properties"

	/** @var TrainingService */
	protected $trainingService;

	/** @var Service */
	protected $service;

	/** @var ConfigurationItem */
	protected $selectedConfigurationItem = null;

	/** @var array Values to be stored in ConfigurationItem object */
	private $configurationItemValues = array('name', 'isGlobal', 'code');

	/** @var array Values to be stored in ConfigurationItemSpecification object */
	private $specificationValues = array('priority', 'onPingRaw', 'onInputReceivedRaw', 'onRestartRaw', 'onReplaceRaw', 'operationalCosts', 'purchaseCosts');

	/** @var array Cache of JavaScript transformed events */
	private $compiledEvents = array();

	#endregion

	#region "Lifecycle methods"

	/**
	 * Startup method.
	 */
	public function startup() {
		parent::startup();

		if ($this->isAjax()) {
			$this->setLayout('empty');
		}
	}

	/**
	 * @param TrainingService $trainingService
	 */
	public function inject(TrainingService $trainingService) {
		$this->trainingService = $trainingService;
	}

	#endregion

	#region "New CI"

	/**
	 * Create new configuration item.
	 * @param int $serviceId Service ID
	 */
	public function actionNew($serviceId) {
		$this->loadService($serviceId);

		if ($this['configurationItemForm']->isSubmitted()) {
			$this->invalidateControl('ciForm');
		}
		$this->template->showIOSelection = false;
	}

	#endregion

	#region "Edit CI"

	/**
	 * Edit existing configuration item in selected service.
	 * @param int $id Configuration item ID
	 * @param int $serviceId Service ID
	 * @throws \Nette\Application\BadRequestException
	 */
	public function actionEdit($id, $serviceId) {
		$configurationItem = $this->trainingService->getConfigurationItem($id);
		if (!$configurationItem)
			throw new BadRequestException('Invalid configuration item ID.');

		$this->loadService($serviceId);

		if (!$configurationItem->getServices()->exists(function($key, Service $el) use($serviceId) { return $el->getId() == $serviceId; }))
			throw new BadRequestException('CI does not belong to selected service.');

		$this->selectedConfigurationItem = $configurationItem;
		if (!$this['configurationItemForm']->isSubmitted()) {
			$this->populateConfigurationItemForm($configurationItem);

		} else {
			$this->invalidateControl('ciForm');
		}
	}

	#endregion

	#region "Assign CI"

	/**
	 * Assign existing global configuration item to service
	 * @param int $serviceId
	 */
	public function actionAssign($serviceId) {
		$this->loadService($serviceId);

		$this->template->optionsAvailable = (bool) $this->getGlobalConfigurationItems(true);

		if ($this['configurationItemForm']->isSubmitted()) {
			$this->invalidateControl('ciForm');
		}
	}

	#endregion

	#region "Delete CI"

	/**
	 * Delete configuration item. If configuration item is assigned to multiple services, it is only unassigned from
	 * the selected service and kept in all other services.
	 * @param int $id
	 * @param int $serviceId
	 */
	public function actionDelete($id, $serviceId) {
		$this->loadService($serviceId);

		$configurationItem = $this->trainingService->getConfigurationItem($id);
		if ($configurationItem->getServices()->exists(function($key, Service $el) use($serviceId) { return $el->getId() == $serviceId; })) {
			$this->service->removeConfigurationItem($configurationItem);

			if (!$configurationItem->getServices()->count()) {
				$this->trainingService->deleteConfigurationItem($configurationItem);
				$this->flashInfoMessage('Configuration item %s was deleted.', $configurationItem->getName());

			} else {
				$this->flashInfoMessage('Configuration item %s was unassigned.', $configurationItem->getName());
			}
		}

		$this->redirect('service:detail', array('id' => $this->service->getId()));
	}

	#endregion

	#region "CI form component"

	/**
	 * Create configuration item form.
	 * @return Form
	 */
	protected function createComponentConfigurationItemForm() {
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->addText('name', 'Name:')
			->addRule(Form::FILLED)
			->getLabelPrototype()->addClass('required');

		$form->addText('code', 'ID:');

		$form->addSelect('priority', 'Priority:', PriorityEnum::getOptions());
		$form->addText('healthLevel', 'Health level (%):')
			->setType('number')
			->setAttribute('min', 0)
			->setAttribute('max', 100);

		$form->addText('purchaseCosts', 'Purchase costs:')
			->addCondition(Form::FILLED)
				->addRule(Form::FLOAT, 'Purchase costs must be a number.');

		$form->addText('operationalCosts', 'Operational costs:')
			->addCondition(Form::FILLED)
			->addRule(Form::FLOAT, 'Operational costs must be a number.');

		$form->addCheckbox('isGlobal', 'Is global');

		$form->addTextArea('onPingRaw', 'onPing:', 40, 3);
		$form->addTextArea('onInputReceivedRaw', 'onInputReceived:', 40, 3);
		$form->addTextArea('onRestartRaw', 'onRestart:', 40, 3);
		$form->addTextArea('onReplaceRaw', 'onReplace:', 40, 3);

		// load available IO
		$availableIO = $this->trainingService->getAvailableInputsOutputs($this->service->getTrainingId());
		$io = array();
		foreach ($availableIO as $a) {
			$io[$a->getId()] = $a->getName() . ' (' . $a->getCode() . ')';
		}

		$form->addMultiSelect('inputs', 'Inputs:', $io, 6);
		$form->addMultiSelect('outputs', 'Outputs:', $io, 6);

		// custom attributes
		for($i = 0; $i < self::CUSTOM_ATTRIBUTES_COUNT; $i++) {
			$form->addText('attribute_name_' . $i, 'Name:');
			$form->addText('attribute_code_' . $i, 'ID:');
			$form->addText('attribute_value_' . $i, 'Default value:');
			$form->addText('attribute_minimum_' . $i, 'Min value:')
				->addCondition(Form::FILLED)
					->addRule(Form::FLOAT, sprintf($this->translator->translate('Minimum value in attribute %s must be a valid number.'), $i+1));
			$form->addText('attribute_maximum_' . $i, 'Max value:')
				->addCondition(Form::FILLED)
					->addRule(Form::FLOAT, sprintf($this->translator->translate('Maximum value in attribute %s must be a valid number.'), $i+1));
			$form->addText('attribute_unit_' . $i, 'Unit:');
		}

		$this->template->attributesCount = self::CUSTOM_ATTRIBUTES_COUNT;
		$this->template->currency = TemplateHelpers::emptyCurrency();

		$form->addSubmit('save', 'Save configuration item');
		$form->onSuccess[] = $this->onConfigurationItemFormSuccess;
		$form->onValidate[] = $this->onConfigurationItemFormValidate;

		return $form;
	}

	/**
	 * Validate configuration item form.
	 * @param Form $form
	 */
	public function onConfigurationItemFormValidate(Form $form) {
		$values = $form->getValues();

		// transform javascript to php
		$editors = array('onPing', 'onInputReceived', 'onRestart', 'onReplace');
		foreach ($editors as $editor) {
			try {
				$this->compiledEvents[$editor] = $values[$editor . 'Raw'] ? $this->translateJavaScript($form, $editor . 'Raw') : NULL;

			} catch(\Exception $error) {
				$form->addError($editor . ': ' . $error->getMessage());
			}
		}
	}

	/**
	 * Save configuration item.
	 * @param Form $form
	 */
	public function onConfigurationItemFormSuccess(Form $form) {
		$values = $form->getValues();

		$isNew = !$this->selectedConfigurationItem;

		$ci = $isNew ? new ConfigurationItem() : $this->selectedConfigurationItem;

		$specification = $ci->getDefaultSpecification();
		if (!$specification)
			$specification = new ConfigurationItemSpecification();

		foreach ($this->configurationItemValues as $key) {
			$ci->$key = $values[$key];
		}

		foreach ($this->specificationValues as $key) {
			$specification->$key = $values[$key];
		}

		$specification->setOnPing($this->compiledEvents['onPing']);
		$specification->setOnInputReceived($this->compiledEvents['onInputReceived']);
		$specification->setOnRestart($this->compiledEvents['onRestart']);
		$specification->setOnReplace($this->compiledEvents['onReplace']);

		$specification->clearAttributes();

		// custom attributes
		for ($i = 0; $i < self::CUSTOM_ATTRIBUTES_COUNT; $i++) {
			if ($values['attribute_name_' . $i] && $values['attribute_code_' . $i]) {
				$attr = new CustomServiceAttribute(
								$values['attribute_name_' . $i],
								$values['attribute_code_' . $i],
								$values['attribute_value_' . $i],
								$values['attribute_minimum_' . $i],
								$values['attribute_maximum_' . $i],
								$values['attribute_unit_' . $i]
						);
				$specification->setAttribute($values['attribute_code_' . $i], $attr);
			}
		}

		if ($this->service)
			$ci->addService($this->service);

		$ci->clearOutputs();
		foreach($values['outputs'] as $output) {
			$io = $this->trainingService->getInputOutput($output);
			if ($io)
				$ci->addOutput($io);
		}

		$ci->clearInputs();
		foreach($values['inputs'] as $input) {
			$io = $this->trainingService->getInputOutput($input);
			if ($io)
				$ci->addInput($io);
		}

		$specification->setConfigurationItem($ci);

		$this->trainingService->updateConfigurationItem($ci);
		$this->trainingService->updateConfigurationItemDefaultSpecification($specification);

		$this->flashInfoMessage('Configuration item %s was saved.', $ci->getName());

		$this->template->isSaved = true;
	}

	#endregion

	#region "CI assign existing form component"

	/**
	 * Form to assign global configuration item
	 * @return Form
	 */
	protected function createComponentAssignConfigurationItemForm() {
		$form = new Form();
		$form->setTranslator($this->translator);

		$options = $this->getGlobalConfigurationItems(true);
		$form->addSelect('id', 'Configuration item:', $options)
			->addRule(Form::FILLED);

		$form->addSubmit('save', 'Assign configuration item');
		$form->onSuccess[] = $this->onAssignConfigurationFormSuccess;

		return $form;
	}

	/**
	 * Assign global configuration item to service
	 * @param Form $form
	 */
	public function onAssignConfigurationFormSuccess(Form $form) {
		$values = $form->getValues();

		$items = $this->getGlobalConfigurationItems(false);
		if (!isset($items[$values['id']]))
			return;

		$ci = $items[$values['id']];
		$this->service->addConfigurationItem($ci);
		$this->trainingService->updateService($this->service);

		$this->flashInfoMessage('Configuration item %s was assigned.', $ci->getName());

		$this->template->isSaved = true;

		if (!$this->isAjax()) {
			$this->redirect('service:detail', array('id' => $this->service->getId()));
		}
	}

	#endregion

	#region "Helpers"

	/**
	 * Load selected service and set it to $this->service and to template.
	 * @param int $serviceId
	 * @throws \Nette\Application\BadRequestException
	 */
	protected function loadService($serviceId) {
		$this->service = $this->template->service = $this->trainingService->getService($serviceId);

		if ($this->service->getUserId() != $this->getUserId()) {
			throw new BadRequestException('Invalid service ID.');
		}
	}

	/**
	 * Transform JavaScript code to PHP using JavaScriptTranslator.
	 * @param Form $form
	 * @param $fieldName
	 * @return array|null|string
	 * @throws \Exception
	 */
	protected function translateJavaScript(Form $form, $fieldName) {
		$values = $form->getValues();

		$js = new JavaScriptTranslator();

		$compiled = $js->translate($values[$fieldName]);

		$validator = new PHPValidator($this->getHttpRequest()->url->baseUrl);
		if (!$validator->validate($compiled)) {
			throw new \Exception('invalid code');
		}

		return $compiled;
	}

	/**
	 * Populate configuration item form
	 * @param ConfigurationItem $configurationItem
	 */
	protected function populateConfigurationItemForm(ConfigurationItem $configurationItem)
	{
		$defaultValues = array();
		foreach ($this->configurationItemValues as $value) {
			$defaultValues[$value] = $configurationItem->$value;
		}

		foreach ($this->specificationValues as $value) {
			$defaultValues[$value] = $configurationItem->getDefaultSpecification()->$value;
		}

		$defaultValues['inputs'] = array_map(
			function ($e) { return $e->id; },
			$configurationItem->getInputs()->toArray()
		);
		$defaultValues['outputs'] = array_map(
			function ($e) { return $e->id; },
			$configurationItem->getOutputs()->toArray()
		);

		$i = 0;
		// populate custom attributes
		foreach ($this->selectedConfigurationItem->getDefaultSpecification()->getAttributes() as $attribute) {
			$defaultValues['attribute_name_' . $i] = $attribute->getName();
			$defaultValues['attribute_code_' . $i] = $attribute->getCode();
			$defaultValues['attribute_value_' . $i] = $attribute->getCurrentValue();
			$defaultValues['attribute_minimum_' . $i] = $attribute->getMinimumValue();
			$defaultValues['attribute_maximum_' . $i] = $attribute->getMaximumValue();
			$defaultValues['attribute_unit_' . $i] = $attribute->getUnit();
			$i++;
		}

		$this['configurationItemForm']->setDefaults($defaultValues);
	}

	/**
	 * Return available global configuration items
	 * @param bool $asKeyValueArray TRUE to return as associative array (ID => name)
	 * @return array|ConfigurationItem[]
	 */
	protected function getGlobalConfigurationItems($asKeyValueArray) {
		$availableConfigurationItems = $this->trainingService->getConfigurationItemsByService(0, $this->getUserId());
		$options = array();
		foreach ($availableConfigurationItems as $ci) {
			$options[$ci->getId()] = $asKeyValueArray ? sprintf('%s: %s', $ci->getCode(), $ci->getName()) : $ci;
		}

		return $options;
	}

	#endregion
}