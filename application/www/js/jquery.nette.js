/**
 * AJAX Nette Framwork plugin for jQuery
 *
 * @copyright  Copyright (c) 2009, 2010 Jan Marek
 * @copyright  Copyright (c) 2009, 2010 David Grudl
 * @license    MIT
 * @link       http://nette.org/cs/extras/jquery-ajax
 */

/*
 if (typeof jQuery != 'function') {
 alert('jQuery was not loaded');
 }
 */

(function ($) {

  $.nette = {
    success: function (payload) {
      if (payload) {
        if (payload.redirect) {
          window.location.href = payload.redirect;
          return;
        }

        if (payload.state) {
          $.nette.state = payload.state;
        }

        if (payload.snippets) {
          for (var i in payload.snippets) {
            $.nette.updateSnippet(i, payload.snippets[i]);
          }
        }
      }

      // change URL (requires HTML5)
      if (window.history && history.pushState && $.nette.href) {
        //history.pushState({href: $.nette.href}, '', $.nette.href);
      }

      if (window.itil && itil.ajax && itil.ajax.afterRequest && itil.ajax.afterRequest.length) {
        var l = itil.ajax.afterRequest.length;
        for (var i = 0; i < l; i++) {
          itil.ajax.afterRequest[i].apply();
        }
      }
    },

    updateSnippet: function (id, html) {
      $('#' + id).html(html);
      var forms = $('form', '#' + id);
      if (forms.length) {
        for (var i = 0; i < forms.length; i++)
          Nette.initForm(forms.get(i));
      }
    },

    // create animated spinner
    createSpinner: function (id) {
      return this.spinner = $('<div></div>').attr('id', id ? id : 'ajax-spinner').ajaxSend(function (evt, request, settings) {
        if (settings.url.indexOf('ping') < 0 && settings.url.indexOf('reloadServices') < 0)
          $(this).show();

      }).ajaxComplete(function (evt, request, settings) {
          if (settings.url.indexOf('ping') < 0 && settings.url.indexOf('reloadServices') < 0) {
            $(this).hide().css({
              position: 'fixed',
              left: '50%',
              top: '50%'
            });
          }

        }).appendTo('body').hide();
    },

    // current page state
    state: null,
    href: null,

    // spinner element
    spinner: null
  };


})(jQuery);


jQuery(function ($) {
  // HTML 5 popstate event
  $(window).bind('popstate', function (event) {
    $.nette.href = null;
    if (event.originalEvent.state) {
      $.post(event.originalEvent.state.href, $.nette.success);
    }
  });

  $.ajaxSetup({
    success: $.nette.success,
    dataType: 'json'
  });

  $.nette.createSpinner();

  // apply AJAX unobtrusive way
  $('a.ajax').live('click', function (event) {
    event.preventDefault();
    //if ($.active) return;

    if ($(this).attr('data-confirm')) {
      if (!confirm($(this).attr('data-confirm')))
        return false;
    }

    var href = this.href;
    $.get(href, function (payload) {
      $.nette.href = href;
      $.nette.success(payload);
    });

    if (event.pageX) {
      $.nette.spinner.css({
        position: 'absolute',
        left: event.pageX,
        top: event.pageY
      });
    }
  });

});
