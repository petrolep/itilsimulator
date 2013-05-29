/**
 * ITIL Simulator project
 * 2013 Petr Dvorak, petrdvorak.cz
 */

itil = window.itil || {};

/**
 * Basic simulator GUI and helpers initialization.
 */

(function() {
  // custom global storage for request scope data
  itil.currentPage = {};

  /** Callback to be executed by Nette after ajax request **/
  itil.ajax = {};
  itil.ajax.afterRequest = [];

  /**
   * GUI initialization
   */
  itil.init = function() {
    var initLists = function() {
      $('.expandable .item').live('click', function(e) {
        if (e.target.nodeName.toLowerCase() != 'a')
          $(this).find('.expand').slideToggle();
      });
      $('.expandable .expand').hide();
      $('.expandable .expanded').show();
    };

    var initClickableElements = function(elem) {
      elem.addClass('js');
      elem.hover(function() { $(this).addClass('hover'); }, function() { $(this).removeClass('hover');});
      elem.click(function() {
        var a = $(this).find('a:eq(0)');
        if (a.length) {
          window.location.href = a.attr('href');
        }
      });
    };

    itil.tabs($('.tabs'));
    initLists();
    initClickableElements($('.clickable'));

    itil.flash.display();
  };

  /**
   * GUI tabs
   * @param elems jQuery elements collection
   */
  itil.tabs = function(elems) {
    elems.each(function() {
      var parseId = function(value) {
        return value.substr(value.indexOf('#'));
      };

      var that = $(this);
      var all = $(this).find('li');
      var firstTab = all.find('a:eq(0)').get(0);
      all.find('a').click(function() {
        // hide all tabs
        all.removeClass('active');
        that.find('a').each(function() {
          $(parseId($(this).attr('href'))).hide();
        });

        // show current tab
        $(parseId($(this).attr('href'))).show();
        $(this).parent().addClass('active');

        if (this == firstTab) {
          that.next('.tabs-content:eq(0)').addClass('first-tab-active');
        } else {
          that.next('.tabs-content:eq(0)').removeClass('first-tab-active');
        }

        return false;
      });

      $(this).find('a:eq(0)').trigger('click');
    });
  };

  /**
   * Dialogs
   * @type {{counter: number, createOverlay: Function, show: Function, hide: Function, showMultiple: Function}}
   */
  itil.dialog = {
    /**
     * Number of dialogs displayed
     */
    counter: 100,

    /**
     * Create overlay
     * @returns {*|jQuery|HTMLElement}
     */
    createOverlay: function() {
      var overlay = $(document.createElement('div'));
      overlay.addClass('dialog-overlay');
      //overlay.css('top', $('body').scrollTop());
      $('body').append(overlay);

      overlay.click(function(e) {
        e.stopPropagation();
        e.preventDefault();

        return false;
      });

      return overlay;
    },

    /**
     * Show dialog with given content
     * @param content DOM node or string to be displayed
     */
    show: function(content) {
      itil.dialog.counter++;
      var zIndex = itil.dialog.counter;

      if (content.attr && content.attr('data-priority')) {
        // custom priority defined by "data-priority" attribute, increase z-index
        zIndex += parseInt(content.attr('data-priority'), 10) * 100;
      }

      var overlay = itil.dialog.createOverlay();
      overlay.css('z-index', zIndex);

      var dialog = $(document.createElement('div'));
      dialog.css('width', 'auto');
      dialog.addClass('dialog');
      dialog.css('z-index', -1);
      dialog.css('left', 0);

      var minimizeButton = $(document.createElement('span'));
      minimizeButton.addClass('minimize');
      minimizeButton.text('minimize');
      minimizeButton.click(function() {
        dialog.toggleClass('minimized');

        return false;
      });

      dialog.append(minimizeButton);
      dialog.append('<div class="dialog-inner"></div>');
      $('body').append(dialog);
      dialog.find('.dialog-inner').append(content);
      dialog.find('.close').live('click', function(e) {
        itil.dialog.hide(dialog, overlay);

        e.preventDefault();
      });

      $('body').addClass('with-overlay');
      dialog.width(dialog.width());
      dialog.css('left', '50%');
      dialog.css('top', $(document).scrollTop() + $('body').height() / 2);
      dialog.css('margin-left', -dialog.width() / 2);
      dialog.css('margin-top', -dialog.height() / 2);

      var topOffset = dialog.offset().top;
      if (topOffset < 0) {
        dialog.css('margin-top', -dialog.height() / 2 - topOffset);
      }

      dialog.css('z-index', zIndex);

    },

    /**
     * Hide dialog (removes the dialog and overlay from DOM)
     * @param dialog jQuery node of existing dialog
     * @param overlay jQuery node of existing overlay
     * @returns {boolean}
     */
    hide: function(dialog, overlay) {
      var destroy = (dialog === true);
      if (destroy) {
        dialog = null;
      }

      if (!dialog)
        dialog = $('.dialog');
      if (!overlay)
        overlay = $('.dialog-overlay');

      if ($('.dialog').length == 1) {
        $('body').removeClass('with-overlay');
      }

      overlay.fadeOut();
      dialog.fadeOut(function() {
        if(true || destroy) { // TODO
          dialog.remove();
          overlay.remove();
        }
      });

      return false;
    },

    /**
     * Show multiple dialogs
     * @param dialogs Collection of jQuery nodes to be displayed as dialogs
     */
    showMultiple: function(dialogs) {
      if (dialogs.length) {
        dialogs.each(function() {
          itil.dialog.show($(this));
        });
      }
    }
  };

  /**
   * Notifications (flash messages in nette)
   */
  itil.flash = {
    /**
     * Position of notification
     */
    topOffset: 50,
    /**
     * Display notifications
     * @param elems Collection of jQuery elements to be displayed
     */
    display: function(elems) {
      if (!elems)
        elems = $('.flash');

      $('body').append(elems);

      elems.each(function() {
        var elem = $(this);

        if (elems.is('.processed[data-guid=' + elem.attr('data-guid') + ']').length) {
          // same flash message already displayed, ignore
          return;
        }

        if (elem.hasClass('processed'))
          return;

        elem.addClass('processed');

        // set position
        var thisHeight = elem.height() + 30;
        elem.css('bottom', itil.flash.topOffset);
        itil.flash.topOffset += thisHeight;

        // auto fade out after timeout
        setTimeout(function() {
          elem.fadeOut(function() {
            elem.remove();
            itil.flash.topOffset -= thisHeight;
          });
        }, 7000);

        // manual fadeout after click
        elem.click(function() {
          elem.fadeOut();
        });

        elem.hide();
        elem.fadeIn();
      });
    }
  };

  /**
   * Show spinner (ajax loader)
   * @param event
   */
  itil.showSpinner = function(event) {
    if(event && event.pageX) {
      $.nette.spinner.css({ position: 'absolute', left: event.pageX, top: event.pageY }).show();

    } else {
      $.nette.spinner.css({ position: 'fixed', left: '50%', top: '50%' }).show();
    }
  };
})();

$(document).ready(function() {
  // init GUI elements
  itil.init();
});
