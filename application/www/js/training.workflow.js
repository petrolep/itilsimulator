/**
 * ITIL Simulator project
 * 2013 Petr Dvorak, petrdvorak.cz
 */

itil = window.itil || {};

(function() {
itil.workflow = {};
/**
 * Workflow designer module.
 * Based on original jsPlumb demo "State Machine".
 * @param container
 * @param settings
 * @returns {{init: Function, addItems: Function, removeItem: Function, getState: Function}}
 * @constructor
 */
itil.workflow.Designer = function (container, settings) {
  // helper method to generate a color from a cycle of colors.
  var defaultSettings = {
    color: 'rgb(50,86,107)',
    hoverColor: 'rgb(122,146,29)',
    lineWidth: 5,
    itemSelector: '.item',
    handlerSelector: '.handle',
    connections: [],
    onClick: null,
    onConnectionClick: null,
    onConnectionDblClick: null,
    useDatabaseConnections: true,
    submitOnlyUpdated: true
  };
  settings = $.extend({}, defaultSettings, settings);

  var databaseConnections = {};
  var connections = {};
  var deletedConnections = [];

  var initEndpoints = function (container) {
    container.find(settings.handlerSelector).each(function (i, e) {
      var p = $(e).parent();
      jsPlumb.makeSource($(e), {
        parent: p,
        anchor: "Continuous",
        connector: [ "StateMachine", { curviness: 20 } ],
        connectorStyle: { strokeStyle: settings.color, lineWidth: settings.lineWidth },
        maxConnections: 5,
        detachable: true,
        reattach: true,
        onMaxConnections: function (info, e) {
          alert("Maximum connections (" + info.maxConnections + ") reached");
        }
      });
    });
  };

  var initItems = function(items) {
    jsPlumb.draggable(items);

    jsPlumb.makeTarget(items, {
      dropOptions: { hoverClass: "dragHover" },
      anchor: "Continuous"
    });

    if (settings.onClick)
      items.click(settings.onClick);
  };

  return {
    /**
     * Init new workflow designer
     */
    init: function () {
      jsPlumb.importDefaults({
        Endpoint: ["Dot", {radius: 2}],
        HoverPaintStyle: {strokeStyle: settings.hoverColor, lineWidth: settings.lineWidth },
        ConnectionsDetachable:true,
        ReattachConnections:false,
        ConnectionOverlays: [
          [ "Arrow", {
            location: 1,
            id: "arrow",
            length: 14,
            foldback: 0.8
          } ],
          [ "Label", { label: itil.config.resources['flow.dragToTarget'], title: "Ahoj", id: "label" }]
        ]
      });

      // initialise draggable elements.  note: jsPlumb does not do this by default from version 1.3.4 onwards.
      var items = container.find(settings.itemSelector);

      // dblclick causes text selection, disable it
      // http://stackoverflow.com/questions/880512/prevent-text-selection-after-double-click
      var clearSelection = function() {
            if(document.selection && document.selection.empty) {
                document.selection.empty();
            } else if(window.getSelection) {
                var sel = window.getSelection();
                sel.removeAllRanges();
            }
        };

      var doubleClickTimeout = null;
      // on click
      if (settings.onConnectionClick) {
        jsPlumb.bind("click", function (c) {
          if (doubleClickTimeout)
            // waiting for double click
            return;

          var that = $(this);

          doubleClickTimeout = setTimeout(function() {
            doubleClickTimeout = null;
            settings.onConnectionClick.call(that, settings.useDatabaseConnections ? connections[c.id].databaseConnection : c);
          }, 200);
        });
      }

      // on connection double click
      if (settings.onConnectionDblClick) {
        jsPlumb.bind("dblclick", function(e, b) {
          clearSelection();
          b.preventDefault();

          clearTimeout(doubleClickTimeout);
          doubleClickTimeout = null;
          var that = $(this);

          settings.onConnectionDblClick.call(that, e);

          if(document.selection && document.selection.empty) {
              document.selection.empty();
          } else if(window.getSelection) {
              var sel = window.getSelection();
              sel.removeAllRanges();
          }
        });
      }


      var getDatabaseConnectionKey = function(source, target) {
        return source + 'x' + target;
      };

      var lastDroppedConnectionId = 0;

      // new connection established
      jsPlumb.bind("jsPlumbConnection", function (conn) {
        var databaseKey = null;
        if (connections[conn.connection.id]) {
          // updating existing connection from database which has been updated with designer
          databaseKey = connections[conn.connection.id].databaseConnection;

        } else if (lastDroppedConnectionId && lastDroppedConnectionId != conn.connection.id) {
          databaseKey = connections[lastDroppedConnectionId].databaseConnection;
          connections[conn.connection.id] = connections[lastDroppedConnectionId];
          connections[conn.connection.id].id = conn.connection.id;
          delete connections[lastDroppedConnectionId];

          lastDroppedConnectionId = 0;

        } else {
          // updating existing connection from database which has not been updated with designer
          var key = getDatabaseConnectionKey(conn.connection.sourceId, conn.connection.targetId);
          if (databaseConnections[key] && !databaseConnections[key].initialized) {
            databaseKey = databaseConnections[key];
            databaseKey.initialized = true;
          }
        }

        conn.connection.setPaintStyle({strokeStyle: settings.color, lineWidth: settings.lineWidth});
        if (settings.useDatabaseConnections) {
          var l = conn.connection.getOverlay("label");
          if (databaseKey)
            l.addClass('wf-flow-' + databaseKey.id);

          l.setLabel(databaseKey ? (databaseKey.title ? databaseKey.title : '[~]') : itil.config.resources['flow.new']);
        }

        if (databaseKey) {
          databaseKey.connectionId = conn.connection.id;
        }

        var updated = !!connections[conn.connection.id];
        connections[conn.connection.id] = {
          id: conn.connection.id,
          databaseConnection: databaseKey,
          source: conn.connection.sourceId, target: conn.connection.targetId,
          updated: updated
        };
      });

      jsPlumb.bind("jsPlumbConnectionDetached", function (conn) {
        if (connections[conn.connection.id]) {
          if (connections[conn.connection.id].databaseConnection)
            deletedConnections.push(connections[conn.connection.id].databaseConnection);
        } else {
          var key = getDatabaseConnectionKey(conn.connection.sourceId, conn.connection.targetId);
          if (databaseConnections[key]) {
            deletedConnections.push(databaseConnections[key]);
          }
        }
      });

      initItems(items);
      initEndpoints(container);

      // create connections
      var l = settings.connections.length;
      for (var i = 0; i < l; i++) {
        databaseConnections[getDatabaseConnectionKey(settings.connections[i].source, settings.connections[i].target)] = settings.connections[i];
        jsPlumb.connect({ source: settings.connections[i].source, target: settings.connections[i].target, tooltip: Math.random() });
      }

      jsPlumb.bind("connectionDrag", function(connection) {
        lastDroppedConnectionId = connection.id;
          //console.log("connection " + connection.id + " is being dragged");
      });

      jsPlumb.bind("connectionDragStop", function(connection) {
        lastDroppedConnectionId = 0;
      });
    },
    addItems: function(items) {
      initItems(items);
      initEndpoints(items);
    },
    removeItem: function(item) {
      try {
        jsPlumb.select({source: item}).detach();
        jsPlumb.removeAllEndpoints(item);
        jsPlumb.unmakeTarget(item);
        jsPlumb.unmakeSource(item);
        item.remove();
        jsPlumb.repaintEverything();

      } catch(e) {
        // TODO: jsPlumb 1.4.0 should fix the exception
      }
    },
    getState: function() {
      var result = {
        update: [],
        create: [],
        delete: []
      };

      var existingConnections = jsPlumb.getConnections();
      var l = existingConnections.length;
      for (var i = 0; i < l; i++) {
        var item = connections[existingConnections[i].id];
        if (item && item.databaseConnection) {
          // update existing connection
          if (item.updated || !settings.submitOnlyUpdated)
            result.update.push({id: item.databaseConnection.id, source: item.source, target: item.target});

        } else {
          // create new connection
          result.create.push({source: item.source, target: item.target});
        }
      }

      var l = deletedConnections.length;
      for (var i = 0; i < l; i++) {
        result.delete.push({id: deletedConnections[i].id, source: deletedConnections[i].source, target: deletedConnections[i].target});
      }

      return result;
    },
    getPositions: function() {
      var elements = container.find(settings.itemSelector);
      var l = elements.length;
      var result = [];
      for (var i = 0; i < l; i++) {
        var a = elements.eq(i);
        var position = a.position();
        result.push({'id': parseInt(a.attr('data-id'), 10), 'x': position.left, 'y': position.top});
      }

      return result;
    }
  };
};
})();