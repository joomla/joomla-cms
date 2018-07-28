/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Only define the Joomla namespace if not defined.
Joomla = window.Joomla || {};

!(function (document, Joomla) {
  'use strict';

  /**
	 * Sets the HTML of the container-collapse element
	 */
  Joomla.setcollapse = function (url, name, height) {
    if (!document.getElementById(`collapse-${name}`)) {
      document.getElementById('container-collapse').innerHTML = `<div class="collapse fade" id="collapse-${name}"><iframe class="iframe" src="${url}" height="${height}" width="100%"></iframe></div>`;
    }
  };

  /**
	 * JField 'showon' feature.
	 */
  window.jQuery && (function ($) {
    /**
		 * Method to check condition and change the target visibility
		 * @param {jQuery}  target
		 * @param {Boolean} animate
		 */
    function linkedoptions(target, animate) {
      let showfield = true;


      const jsondata = target.data('showon') || [];


      let itemval; let condition; let fieldName; let
        $fields;

      // Check if target conditions are satisfied
      for (var j = 0, lj = jsondata.length; j < lj; j++) {
        condition = jsondata[j] || {};
        fieldName = condition.field;
        $fields = $(`[name="${fieldName}"], [name="${fieldName}[]"]`);

        condition.valid = 0;

        // Test in each of the elements in the field array if condition is valid
        $fields.each(function () {
          const $field = $(this);

          // If checkbox or radio box the value is read from properties
          if (['checkbox', 'radio'].indexOf($field.attr('type')) !== -1) {
            if (!$field.prop('checked')) {
              // unchecked fields will return a blank and so always match a != condition so we skip them
              return;
            }
            itemval = $field.val();
          } else {
            // select lists, textarea etc. Note that multiple-select list returns an Array here
            // se we can always tream 'itemval' as an array
            itemval = $field.val();
            // a multi-select <select> $field  will return null when no elements are selected so we need to define itemval accordingly
            if (itemval == null && $field.prop('tagName').toLowerCase() == 'select') {
              itemval = [];
            }
          }

          // Convert to array to allow multiple values in the field (e.g. type=list multiple)
          // and normalize as string
          if (!(typeof itemval === 'object')) {
            itemval = JSON.parse(`["${itemval}"]`);
          }

          // for (var i in itemval) loops over non-enumerable properties and prototypes which means that != will ALWAYS match
          // see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/for...in
          // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/getOwnPropertyNames
          // use native javascript Array forEach - see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/forEach
          // We can't use forEach because its not supported in MSIE 8 - once that is dropped this code could use forEach instead and not have to use propertyIsEnumerable
          //
          // Test if any of the values of the field exists in showon conditions
          for (const i in itemval) {
            // See https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/propertyIsEnumerable
            // Needed otherwise we pick up unenumerable properties like length etc. and !: will always match one of these  !!
            if (!itemval.propertyIsEnumerable(i)) {
              continue;
            }
            // ":" Equal to one or more of the values condition
            if (jsondata[j].sign == '=' && jsondata[j].values.indexOf(itemval[i]) !== -1) {
              jsondata[j].valid = 1;
            }
            // "!:" Not equal to one or more of the values condition
            if (jsondata[j].sign == '!=' && jsondata[j].values.indexOf(itemval[i]) === -1) {
              jsondata[j].valid = 1;
            }
          }
        });

        // Verify conditions
        // First condition (no operator): current condition must be valid
        if (condition.op === '') {
          if (condition.valid === 0) {
            showfield = false;
          }
        }
        // Other conditions (if exists)
        else {
          // AND operator: both the previous and current conditions must be valid
          if (condition.op === 'AND' && condition.valid + jsondata[j - 1].valid < 2) {
            showfield = false;
          }
          // OR operator: one of the previous and current conditions must be valid
          if (condition.op === 'OR' && condition.valid + jsondata[j - 1].valid > 0) {
            showfield = true;
          }
        }
      }

      // If conditions are satisfied show the target field(s), else hide
      if (animate) {
        (showfield) ? target.slideDown() : target.slideUp();
      } else {
        target.toggle(showfield);
      }
    }

    /**
		 * Method for setup the 'showon' feature, for the fields in given container
		 * @param {HTMLElement} container
		 */
    function setUpShowon(container) {
      container = container || document;

      const $showonFields = $(container).find('[data-showon]');

      // Setup each 'showon' field
      for (var is = 0, ls = $showonFields.length; is < ls; is++) {
        // Use anonymous function to capture arguments
        (function () {
          const $target = $($showonFields[is]); const jsondata = $target.data('showon') || [];


          let field; let
            $fields = $();

          // Collect an all referenced elements
          for (let ij = 0, lj = jsondata.length; ij < lj; ij++) {
            field = jsondata[ij].field;
            $fields = $fields.add($(`[name="${field}"], [name="${field}[]"]`));
          }

          // Check current condition for element
          linkedoptions($target);

          // Attach events to referenced element, to check condition on change
          $fields.on('change', () => {
            linkedoptions($target, true);
          });
        }());
      }
    }

    /**
		 * Initialize 'showon' feature at an initial page load
		 */
    document.addEventListener('DOMContentLoaded', (event) => {
      setUpShowon(event.target);
    });

    /**
		 * Initialize 'showon' feature when part of the page was updated
		 */
    document.addEventListener('joomla:updated', (event) => {
      const $target = $(event.target);

      // Check is it subform, then wee need to fix some "showon" config
      if ($target.hasClass('subform-repeatable-group')) {
        const $elements = $target.find('[data-showon]');


        const baseName = $target.data('baseName');


        const group = $target.data('group');


        const search = new RegExp(`\\[${baseName}\\]\\[${baseName}X\\]`, 'g');


        const replace = `[${baseName}][${group}]`;


        let $elm; let
          showon;

        // Fix showon field names in a current group
        for (let i = 0, l = $elements.length; i < l; i++) {
          $elm = $($elements[i]);
          showon = $elm.attr('data-showon').replace(search, replace);

          $elm.attr('data-showon', showon);
        }
      }

      setUpShowon(event.target);
    });
  }(jQuery));
}(document, Joomla));
