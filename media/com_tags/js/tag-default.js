/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function (document) {
  'use strict';

  // selectors used in this scirpt
  var formId = 'adminForm';
  var searchFilterId = 'filter-search';

  document.addEventListener('DOMContentLoaded', function () {
      var form = document.getElementById(formId);
      form.querySelector('button[type="reset"]').addEventListener('click', function(event) {
        document.getElementById(searchFilterId).value = '';
        form.submit();
      })
  });
})(document);
