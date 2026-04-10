/**
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * ajaxChosen javascript behavior
 *
 * Used for displaying tags
 *
 * @package     Joomla.JavaScript
 * @since       1.5
 * @version     1.0
 */
(function($) {
  return $.fn.ajaxChosen = function(settings, callback, chosenOptions) {
    var chosenXhr, defaultOptions, options, select;
    if (settings == null) {
      settings = {};
    }
    if (callback == null) {
      callback = {};
    }
    if (chosenOptions == null) {
      chosenOptions = function() {};
    }
    defaultOptions = {
      minTermLength: 3,
      afterTypeDelay: 500,
      jsonTermKey: "term",
      keepTypingMsg: Joomla.Text._('JGLOBAL_KEEP_TYPING'),
      lookingForMsg: Joomla.Text._('JGLOBAL_LOOKING_FOR')
    };
    select = this;
    chosenXhr = null;
    options = $.extend({}, defaultOptions, $(select).data(), settings);
    this.jchosen(chosenOptions ? chosenOptions : {});
    return this.each(function() {
      return $(this).next('.chosen-container').find(".search-field > input, .chosen-search > input").bind('keyup', function() {
        var field, msg, success, untrimmed_val, val;
        untrimmed_val = $(this).val();
        val = $.trim($(this).val());
        msg = val.length < options.minTermLength ? options.keepTypingMsg : options.lookingForMsg + (" '" + val + "'");
        select.next('.chosen-container').find('.no-results').text(msg);
        if (val === $(this).data('prevVal')) {
          return false;
        }
        $(this).data('prevVal', val);
        if (this.timer) {
          clearTimeout(this.timer);
        }
        if (val.length < options.minTermLength) {
          return false;
        }
        field = $(this);
        if (!(options.data != null)) {
          options.data = {};
        }
        options.data[options.jsonTermKey] = val;
        if (options.dataCallback != null) {
          options.data = options.dataCallback(options.data);
        }
        success = options.success;
        options.success = function(data) {
          var items, nbItems, selected_values;
          if (!(data != null)) {
            return;
          }
          selected_values = [];
          select.find('option').each(function() {
            if (!$(this).is(":selected")) {
              return $(this).remove();
            } else {
              return selected_values.push($(this).val() + "-" + $(this).text());
            }
          });
          select.find('optgroup:empty').each(function() {
            return $(this).remove();
          });
          items = callback.apply(null, data);
          nbItems = 0;
          $.each(items, function(i, element) {
            var group, text, value;
            nbItems++;
            if (element.group) {
              group = select.find("optgroup[label='" + element.text + "']");
              if (!group.size()) {
                group = $("<optgroup />");
              }
              group.attr('label', element.text).appendTo(select);
              return $.each(element.items, function(i, element) {
                var text, value;
                if (typeof element === "string") {
                  value = i;
                  text = element;
                } else {
                  value = element.value;
                  text = element.text;
                }
                if ($.inArray(value + "-" + text, selected_values) === -1) {
                  return $("<option />").attr('value', value).html(text).appendTo(group);
                }
              });
            } else {
              if (typeof element === "string") {
                value = i;
                text = element;
              } else {
                value = element.value;
                text = element.text;
              }
              if ($.inArray(value + "-" + text, selected_values) === -1) {
                return $("<option />").attr('value', value).html(text).appendTo(select);
              }
            }
          });
          if (nbItems) {
            select.trigger("chosen:updated");
          } else {
            select.data().jchosen.no_results_clear();
            select.data().jchosen.no_results(field.val());
          }
          if (success != null) {
            success(data);
          }
          return field.val(untrimmed_val);
        };
        return this.timer = setTimeout(function() {
          if (chosenXhr) {
            chosenXhr.abort();
          }
          return chosenXhr = $.ajax(options);
        }, options.afterTypeDelay);
      });
    });
  };
})(jQuery);

jQuery(document).ready(function ($) {
  if (Joomla.getOptions('ajax-chosen')) {

    var options = Joomla.getOptions('ajax-chosen');

    $(options.selector).ajaxChosen({
      type: options.type,
      url: options.url,
      dataType: options.dataType,
      jsonTermKey: options.jsonTermKey,
      afterTypeDelay: options.afterTypeDelay,
      minTermLength: options.minTermLength
    }, function (data) {
      var results = [];

      $.each(data, function (i, val) {
        results.push({ value: val.value, text: val.text });
      });

      return results;
    });
  }
});
