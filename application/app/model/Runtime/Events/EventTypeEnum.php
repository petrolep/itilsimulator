<?php
/**
 * EventTypeEnum.php
 * 2013 Petr Dvorak, petr-dvorak.cz / 15.4.13 21:09
 */

namespace ITILSimulator\Runtime\Events;


/**
 * Available types of events which can be used with EventManager
 * @package ITILSimulator\Runtime\Events
 */
class EventTypeEnum
{
	const ACTIVITY_MESSAGE_CREATED = 'message.created';
	const ACTIVITY_MESSAGE_PENDING = 'message.pending';
	const ACTIVITY_MESSAGE_ACCEPTED = 'message.accepted';

	const ACTIVITY_INCIDENT_CREATED = 'incident.created';
	const RUNTIME_INCIDENT_ACCEPTED = 'incident.accepted';
	const RUNTIME_INCIDENT_ESCALATED = 'incident.escalated';
	const RUNTIME_INCIDENT_FIX_APPLIED = 'incident.fixed';
	const RUNTIME_INCIDENT_WORKAROUND_APPLIED = 'incident.workaround';
	const RUNTIME_INCIDENT_CLOSED = 'incident.closed';
	const ACTIVITY_INCIDENT_CHANGE = 'incident.change';

	const ACTIVITY_PROBLEM_CREATED = 'problem.created';
	const RUNTIME_PROBLEM_KNOWN_ERROR_REQUESTED = 'problem.knownErrorRequested';
	const RUNTIME_PROBLEM_RFC_REQUESTED = 'problem.rfcRequested';
	const ACTIVITY_PROBLEM_CHANGE = 'problem.change';

	const SERVICE_EVENT_CREATED = 'event.created';
	const RUNTIME_EVENT_ARCHIVED = 'event.archived';

	const ACTIVITY_EVALUATION_CREATED = 'evaluation.created';

	const WORKFLOW_FINISHED = 'workflow.finished';

	const CONFIGURATION_ITEM_CHANGE = 'ci.change';
	const CONFIGURATION_ITEM_REQUEST = 'ci.request';
	const RUNTIME_CONFIGURATION_ITEM_RESTARTED = 'ci.restarted';
	const RUNTIME_CONFIGURATION_ITEM_REPLACED = 'ci.replaced';

	const SCENARIO_FINISHED = 'scenario.finished';

}