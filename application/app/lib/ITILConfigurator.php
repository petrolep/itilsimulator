<?php
/**
 * ITILConfigurator.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 12.5.13 15:23
 */

namespace ITILSimulator\Base;

/**
 * Custom configurator for the application. Initialized via config.neon file.
 * @package ITILSimulator\Base
 */
class ITILConfigurator
{
	protected $defaultConfig = array(
		'hashMethod' => 'sha1', // password hash method
		'currency' => 'Kč', // currency used in templates
		'defaultWorkflowName' => '[Workflow %s]', // %s will be replaced for current date
		'allowPublicHomepage' => true, // allow browsing of public events for not-authenticated users
		'assignAnonymousUsersCreatorRole' => true, // assign anonymous users the Creator role to access Creator Module
		'escalationCost' => 100, // cost for escalating the incident
		'graphHistoryLimit' => 1800, // graph records history (in seconds)
		'scenarioStepTimeout' => 600, // timeout of scenario step (for case user left the scenario) (in seconds)
		'accountingInterval' => 60, // interval of generating bank statements (in seconds)
		'designScenarioPoints' => 20, // points awarded for Service Design scenario
	);

	protected $config;

	public function __construct($config) {
		$this->config = array_merge($this->defaultConfig, $config);

		TemplateHelpers::$currency = $this->config['currency'];
	}

	/**
	 * Password hash method (sha1/md5/etc.)
	 * @return string
	 */
	public function getHashMethod() {
		return $this->config['hashMethod'];
	}

	/**
	 * Current currency
	 * @return string
	 */
	public function getCurrency() {
		return $this->config['currency'];
	}

	/**
	 * Default workflow name
	 * @return string
	 */
	public function getDefaultWorkflowName() {
		return $this->config['defaultWorkflowName'];
	}

	/**
	 * Whether public trainings can be browsed without logging in
	 * @return bool
	 */
	public function getAllowPublicHomepage() {
		return (bool)$this->config['allowPublicHomepage'];
	}

	/**
	 * Whether anonymous users should be assign Creator role and gain access to Creator zone
	 * @return bool
	 */
	public function getAssignAnonymousUsersCreatorRole() {
		return (bool)$this->config['assignAnonymousUsersCreatorRole'];
	}

	/**
	 * Escalation cost
	 * @return float
	 */
	public function getEscalationCost() {
		return $this->config['escalationCost'];
	}

	/**
	 * History of records displayed in graphs (in seconds)
	 * @return int
	 */
	public function getGraphHistoryLimit() {
		return $this->config['graphHistoryLimit'];
	}

	/**
	 * Scenario step timeout, in case the user left scenario (in seconds)
	 * @return int
	 */
	public function getScenarioStepTimeout() {
		return $this->config['scenarioStepTimeout'];
	}

	/**
	 * Interval of generating bank statements (in seconds)
	 * @return int
	 */
	public function getAccountingInterval() {
		return $this->config['accountingInterval'];
	}

	/**
	 * Points awarded for Service Design scenario
	 * @return int
	 */
	public function getDesignScenarioPoints() {
		return $this->config['designScenarioPoints'];
	}
}