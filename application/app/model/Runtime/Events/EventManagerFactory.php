<?php
/**
 * EventManagerFactory.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 15.4.13 20:35
 */

namespace ITILSimulator\Runtime\Events;

/**
 * Factory to create an EventManager instance
 * @package ITILSimulator\Runtime\Events
 */
class EventManagerFactory
{
	/**
	 * @return EventManager
	 */
	public function createEventManager() {
		return new EventManager();
	}
}