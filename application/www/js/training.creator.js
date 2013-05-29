/**
 * ITIL Simulator project
 * 2013 Petr Dvorak, petrdvorak.cz
 */

itil = window.itil || {};

(function(){
/**
 * Creator module
 * @returns {{init: Function, editor: Function}}
 * @constructor
 */
itil.Creator = function() {
  var internal = {
    /**
     * Init inline forms (submitted by ajax)
     */
    initForms: function() {
      $('.inline-form-wrapper form').hide();
      $('.inline-form-link').click(function() {
        $(this).parents('.inline-form-wrapper:eq(0)').find('form').slideToggle();

        return false;
      });
    },

    /**
     * Init behavioral editor and ajax editing
     */
    initTools: function() {
      // ajax form dialogs
      $('.ajax-dialog-edit').live('click', function() {
        $.get($(this).attr('href'), function(response) {
          itil.dialog.show(response);
        }, 'html');
        return false;
      });

      // behavioral editor
      $('.behavioral-editor .expand').live('click', function() {
        var p = $(this).parent('.behavioral-editor');
        var form = $(this).parents('form:eq(0)');

        if (p.hasClass('expanded')) {
          p.removeClass('expanded');
          $(this).text(itil.config.resources.expand);

          if (form.length) {
            // restore form height
            form.css('height', 'auto');
          }

        } else {
          if (form.length) {
            // fix form height
            form.height(form.height());
          }

          p.addClass('expanded');
          $(this).text(itil.config.resources.collapse);
        }

        var textarea = p.find('textarea');
        if(textarea.length && textarea[0].editorReference) {
          textarea[0].editorReference.refresh();
        }

        return false;
      });

      // dialogs max width
      $('head').append('<style type="text/css">.dialog {max-width: ' + ($(window).width() - 100) + 'px;}</style>');
    }
  };

  var editorConfigurations = [];
  var editorInstances = 0;

  return {
    init: function() {
      /** call after DocumentReady! */
      internal.initForms();
      internal.initTools();
    },

    /**
     * Create new behavioral editor
     * @param element jQuery element with target editor
     * @param configuration String Desired configuration
     */
    editor: function(element, configuration) {
      // store configuration for later use
      editorConfigurations.push(configuration);
      element.editorInstanceId = editorInstances;
      editorInstances++;

      CodeMirror.commands.autocomplete = function(cm) {
        CodeMirror.showHint(
          cm,
          CodeMirror.javascriptHint, {
            // load appropriate configuration based on editor's instance ID
            additionalContext: itil.config.getEditorAutocomplete(editorConfigurations[cm.getTextArea().editorInstanceId])
          }
        );
      };

      var editor = CodeMirror.fromTextArea(element, {
        lineNumbers: true,
        extraKeys: {"Ctrl-Space": "autocomplete"}
      });

      editor.on("change", function() {
        // copy value back to original textarea
        editor.save();
      });

      // store reference to CodeMirror to elements DOM node
      element.editorReference = editor;
    }
  }
}
})();