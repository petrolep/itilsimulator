<?php
/**
 * EventManager.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 15.4.13 20:21
 */

namespace ITILSimulator\Runtime\Events;

use ITILSimulator\Runtime\Training\ActiveConfigurationItem;

/**
 * EventManager for publishing and subscribing to events of various types.
 * Events are fired by workflow or UI.
 * @package ITILSimulator\Runtime\Events
 */
class EventManager
{
	/**
	 * Event channel listeners
	 * @var array
	 */
	protected $listeners = array('*' => array());

	/**
	 * IO channel listeners
	 * @var array
	 */
	protected $ioListeners = array();

	#region "Event channel"

	/**
	 * Dispatch new event through Event channel
	 * @param $eventName
	 * @param Event $event
	 */
	public function dispatch($eventName, Event $event) {
		$listeners = $this->listeners['*'];

		if (array_key_exists($eventName, $this->listeners)) {
			$listeners = array_merge($listeners, $this->listeners[$eventName]);
		}

		if (!$listeners)
			return;

		$event->setName($eventName);

		foreach ($listeners as $listener) {
			call_user_func($listener, $event);

			if ($event->isPropagationStopped()) {
				break;
			}
		}
	}

	/**
	 * Add new listener to Event channel
	 * @param $eventName
	 * @param $callback
	 */
	public function addListener($eventName, $callback) {
		$eventName = (string) $eventName;
		if (!array_key_exists($eventName, $this->listeners))
			$this->listeners[$eventName] = array();

		$this->listeners[$eventName][] = $callback;
	}

	#endregion

	#region "IO channel"

	/**
	 * Dispatch event through IO channel
	 * @param $ioId
	 */
	public function dispatchIO($ioId) {
		if (!isset($this->ioListeners[$ioId]))
			return;

		foreach($this->ioListeners[$ioId] as $item) {
			/** @var $item ActiveConfigurationItem */
			$item->receiveInput($ioId);
		}
	}

	/**
	 * Add new listener to IO channel
	 * @param $ioId
	 * @param ActiveConfigurationItem $item
	 */
	public function addIOListener($ioId, ActiveConfigurationItem $item) {
		if (!isset($this->ioListeners[$ioId]))
			$this->ioListeners[$ioId] = array();

		$this->ioListeners[$ioId][] = $item;
	}

	#endregion
}