/**
 * ITIL Simulator project
 * 2013 Petr Dvorak, petrdvorak.cz
 */

itil = window.itil || {};

(function() {
/**
 * Frontend module
 * @returns {{init: Function, pause: Function, isPaused: Function, resume: Function}}
 * @constructor
 */
itil.Frontend = function() {
  var dialogClass = 'to-dialog';
  var dialogSelector = '.to-dialog';
  var processedDialogClass = 'to-dialog-processed';

  var displayedDialogs = [];

  var internal =  {
    /**
     * Init splitter (vertical separator)
     * @param elem jQuery element with separator
     * @param wrapper jQuery element with separator wrapper
     */
    initSplitter: function(elem, wrapper) {
      if (elem.length) {
        var redrawServiceDesk = function() {
          // update height of panels below separator

          var h = $('#working-zone').height() - $('.tabs-content', '#operations').position().top - 25;
          var serviceDeskHeight = h - $('#service-desk .list-footer:eq(0)').height();
          $('#service-desk').height(serviceDeskHeight);
          $('#evaluation-data').height(h - parseInt($('#evaluation-data').css('padding'), 10) * 2);

          var limitedServiceDeskHeight = serviceDeskHeight - $('.tabs', '#operations').height();
          $('.list-wrapper', '#desk-event-mgmt').height(limitedServiceDeskHeight);

          $('#monitoring-placeholder').height(h - 80);
          $('#statistics-placeholder').height(h - 80);
        };

        var redrawServiceCatalog = function() {
          if (!$('#service-catalog-design').length) {
            return;
          }

          var serviceHeight = $('#service-catalog-design').height() + parseInt($('#service-catalog-design').css('margin-top'), 10);
          var catalogHeight = $('#catalog').height();
          if (serviceHeight > catalogHeight) {
            itil.currentPage.serviceCatalogZoom = catalogHeight / serviceHeight;
            if (itil.currentPage.serviceCatalogZoom > 0.9)
              itil.currentPage.serviceCatalogZoom = 1;
            $('#service-catalog-design-wrapper').css('zoom', itil.currentPage.serviceCatalogZoom);
          }
        };

        var redrawComponents = function() {
          redrawServiceDesk();
          redrawServiceCatalog();
        };

        wrapper.height($('body').height() - parseInt(wrapper.css('padding-top'), 10));
        elem.splitter({type: "h", onResize: redrawComponents});
        $('body').addClass('with-splitter');

        redrawComponents();
      }
    },

    /**
     * Show dialogs
     * @param dialogs jQuery elements to be displayed
     */
    showDialogs: function(dialogs) {
      var l = dialogs.length;
      for (var i = 0; i < l; i++) {
        // check if the dialog has not been displayed
        var guid = dialogs.eq(i).attr('data-guid');
        if (guid) {
          if (displayedDialogs.indexOf(guid) >= 0) {
            // check unique identifier (if the message has not been displayed)
            if (dialogs.eq(i).attr('data-force')) {
              // force dialog to display again

            } else {
              // already displayed, remove the new dialog
              dialogs.eq(i).remove();
            }

          } else {
            displayedDialogs.push(guid);
          }
        }
      }

      dialogs = $(dialogSelector);
      if (dialogs.length) {
        itil.dialog.showMultiple(dialogs);
        dialogs.addClass(processedDialogClass);
        dialogs.removeClass(dialogClass);
      }
    },

    /**
     * Init dialogs already presented after page load
     */
    initDialogs: function() {
      // show dialogs loaded on page load
      this.showDialogs($(dialogSelector));

      var that = this;
      itil.ajax.afterRequest.push(function() {
        // show dialogs loaded via ajax
        that.showDialogs($(dialogSelector));
      });
    },

    /**
     * Init "undo" link
     */
    initUndoLink: function() {
      $('#undo-link').click(function() {
        var that = $(this);
        $.get($(this).attr('href'), function(response) {
          var div = $(document.createElement('div'));
          div.addClass('undo-list');
          div.click(function() {
            div.remove();
          });
          div.css('top', that.position().top + 20);
          div.css('left', that.position().left);

          div.append(response);
          $('body').append(div);
        }, 'html');

        return false;
      });
    },

    /**
     * GUI help
     */
    initGUIHelp: function() {
      var step = 0;
      var stepsWrapper = $('.steps', '#operations-help');
      var steps = $('.step', '#operations-help');
      steps.hide();

      var switchHelpStep = function() {
        $('body').removeClass('help-step-' + step);
        step++;
        if (step > steps.length) {
          stepsWrapper.fadeOut();
          step = 0;

          if(window.app)
            app.resume();

          return false;
        }

        if (step == 2) {
          itil.dialog.show('<div class="ui-message info"><span class="title">Toto je ukázková zpráva</span><p>Znění úkolu nebo zpětná vazba</p></div> <div class="buttons"><a class="close" href="">Zavřít</a></div>');
        } else if (step == 4) {
          $('#service-catalog .item .expand').slideDown();
        }

        steps.hide();
        steps.eq(step - 1).show();
        $('body').addClass('help-step-' + step);
      }

      $('#operations-help-link').click(function() {
        stepsWrapper.fadeIn();
        step = 0;
        switchHelpStep();
        if (window.app)
          app.pause();
      });

      $('#operations-help-next-step').click(function() {
        switchHelpStep();

        return false;
      })
    },

    /**
     * Init SLA clocks (timeout for reaction and incident resolution)
     */
    initClock: function() {
      setInterval(function() {
        $('.clocks').each(function() {
          var timeout = $(this).attr('data-timeout');
          if (timeout && timeout < itil.currentPage.internalTime) {
            $(this).addClass('clocks-expired');
          } else {
            $(this).removeClass('clocks-expired');
          }

          var m = (timeout - itil.currentPage.internalTime) / 60;
          var s = (timeout - itil.currentPage.internalTime) % 60;
          var mAbs = Math.floor(Math.abs(m));
          var sAbs = Math.abs(s);
          $(this).text((s < 0 || m < 0 ? '-' : '') + mAbs + ':' + (sAbs < 10 ? '0' + sAbs : sAbs));
        });
        itil.currentPage.internalTime++;
      }, 1000);
    },

    /**
     * Init "service history" and "my history" tabs
     */
    initMonitoring: function() {
      var isMonitoringActivated = false;
      $('#operations .tabs .monitoring').click(function() {
        if (!isMonitoringActivated) {
          $('#link-activate-monitoring').trigger('click');
          isMonitoringActivated = true;
        }
      });

      var isStatisticsActivated = false;
      $('#operations .tabs .statistics').click(function() {
        if (!isStatisticsActivated) {
          $('#link-activate-statistics').trigger('click');
          isStatisticsActivated = true;
        }
      });
    },

    /**
     * Init custom service graphic design.
     */
    initServiceGraphics: function() {
      var itemsContainer = $('#service-catalog-items');

      // timeout for tooltips
      var initTooltipTimeoutCallback = function(tooltip, that) {
        var timeout = setTimeout(function() {
          tooltip.remove();
          that.data('tooltipTimeout', false);
        }, 1000);
        that.data('tooltipTimeout', timeout);
      };

      var clearTooltipTimeoutCallback = function(that) {
        clearTimeout(that.data('tooltipTimeout'));
      };

      // make configuration items hoverable
      $('.visual-item', '#service-catalog-design').hover(function(e) {
        var ciId = $(this).attr('data-ci');
        if (!ciId)
          return;

        var existingCi = $('.ci-item[data-ci="' + ciId + '"]', '#service-catalog');
        if (!existingCi.length)
          return;

        if ($(this).data('tooltipTimeout')) {
          clearTooltipTimeoutCallback($(this));

          return;
        }

        itemsContainer.empty();
        var d = $(document.createElement('div'));
        d.addClass('visual-item-detail');
        d.html(existingCi.html());
        var offset = $('#service-catalog-design-wrapper').offset();

        var top = e.clientY - offset.top;
        if (top < 0)
          top = 0;
        d.css('top', top);

        var left = e.clientX - offset.left;
        if (left + 290 > $('#catalog').width())
          left -= 290;
        if (left < 0)
          left = 0;
        d.css('left', left);

        $(this).data('tooltip', d);

        var that = $(this);
        d.hover(function() {
          clearTooltipTimeoutCallback(that);

        }, function() {
          initTooltipTimeoutCallback($(this), that);
        });

        itemsContainer.append(d);

      }, function(e) {
        var tooltip = $(this).data('tooltip');
        if (!tooltip)
          return;

        initTooltipTimeoutCallback(tooltip, $(this));
      });
    }
  }

  var isPaused = false;
  return {
    init: function() {
      /** call after DocumentReady! */
      internal.initSplitter($('#splitter'), $('#splitter-wrapper'));
      internal.initDialogs();
      internal.initUndoLink();
      internal.initGUIHelp();
      internal.initClock();
      internal.initMonitoring();
    },
    /**
     * Pause application no "ping requests" are being sent
     * @returns {boolean}
     */
    pause: function() {
      return isPaused = true;
    },
    /**
     * Whether the application sends "ping requests" or not
     * @returns {boolean}
     */
    isPaused: function() {
      return isPaused;
    },
    /**
     * Resume paused application "ping request" are being sent
     * @returns {boolean}
     */
    resume: function() {
      isPaused = false;

      return true;
    },
    /**
     * Custom service catalog design
     */
    customGraphicDesign: {
      /**
       * Redraw custom service catalog design
       */
      redraw: function() {
        internal.initServiceGraphics();
      },
      /**
       * Change state of CI in custom service catalog design
       * @param code
       * @param status
       */
      change: function(code, status) {
        var items = $('.visual-item[data-ci="' + code + '"]');
        if (items.length) {
          if (status < 90) {
            items.addClass('highlighted');

          } else {
            items.removeClass('highlighted');
          }
        }
      }
    }
  }
}
})();