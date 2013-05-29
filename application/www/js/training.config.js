/**
 * ITIL Simulator project
 * 2013 Petr Dvorak, petrdvorak.cz
 */

itil = window.itil || {};

(function() {
  /**
   * Configuration for ITIL Simulator
   * @type {{getEditorAutocomplete: Function, resources: {flow.dragToTarget: string, flow.new: string}}}
   */
  itil.config = {
    /**
     * Autocomplete function for CodeMirror editor
     * @param type
     * @returns {*}
     */
    getEditorAutocomplete: function(type) {
      var mathScope = {'min(x,y)': 1, 'max(x,y)':1, 'random()':1, 'abs(x)':1, 'ceil(x)':1, 'floor(x)':1};
      var dateScope = {'internalTime': 1, 'date': 1, 'day': 1, 'fullYear': 1, 'hours': 1, 'milliseconds': 1, 'minutes': 1, 'month': 1, 'seconds': 1, 'time': 1, 'toDateString()': 1, 'toTimeString()': 1};
      var eventScope = {'code': 1, 'description': 1, 'source': 1, 'type': 1 };
      var eventTypeEnumScope = {
        'CONFIGURATION_ITEM_RESTARTED': 1, 'CONFIGURATION_ITEM_REPLACED': 1,
        'EVENT_ARCHIVED': 1,
        'INCIDENT_FIX_APPLIED': 1, 'INCIDENT_WORKAROUND_APPLIED': 1, 'INCIDENT_ESCALATED': 1,
        'INCIDENT_CLOSED': 1,
        'MESSAGE_ACCEPTED': 1,
        'PROBLEM_RFC_REQUESTED' : 1, 'PROBLEM_KNOWN_ERROR_CREATED': 1
      };
      var activityScope = {
        'cancel()': 1,
        'description': 1,
        'finish()': 1,
        'getData(key)': 1,
        'setData(key, value)': 1
      };
      var activityContextScope = {
        'closeIncident(incidentReferenceNumber)': 1,
        'closeProblem(problemReferenceNumber)': 1,
        'getCIValue(serviceCode, configurationItemCode, name)': 1,
        'setCIValue(serviceCode, configurationItemCode, name, value)': 1,
        'solveIncident(incidentReferenceNumber)': 1
      };
      var configurationItemScope = {
        'healthLevel': 1,
        'priority': 1,
        'purchaseCosts': 1,
        'operationalCosts': 1,
        'createEvent(code, description)': 1,
        'getAttribute(key)': 1,
        'getData(key)': 1,
        'setAttribute(key, value)': 1,
        'setData(key, value)': 1,
        'generateOutput(code)': 1
      };

      var mixDefaults = function(options) {
        options.Date = dateScope;
        options.Math = mathScope;

        return options;
      };

      /**
       * Based on type offer corresponding autocomplete options
       */
      switch(type) {
        case 'ci.onPing':
        case 'ci.onRestart':
        case 'ci.onReplace':
          return mixDefaults({
            'this': configurationItemScope
          });

        case 'ci.onInputReceived':
          return mixDefaults({
            'this': configurationItemScope,
            'inputCode': 1
          });

        case 'activity.onEvent':
          return mixDefaults({
            'this': activityScope,
            'context': activityContextScope,
            'event': eventScope,
            'eventTypeEnum': eventTypeEnumScope
          });

        case 'activity.onStart':
        case 'activity.onFinish':
        case 'activity.onCancel':
        case 'activity.onFlow':
          return mixDefaults({
            'this': activityScope,
            'context': activityContextScope
          });

      }
    },
    /**
     * Localized resources (strings)
     */
    resources: {
      'flow.dragToTarget': '',
      'flow.new': ''
    }
  };
})();