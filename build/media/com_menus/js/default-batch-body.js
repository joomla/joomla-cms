/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
jQuery(document).ready(function ($) {
  if ($('#batch-menu-id').length) {
    var batchSelector = $('#batch-menu-id');
  }
  if ($('#batch-copy-move').length) {
    $('#batch-copy-move').hide();
    batchSelector.on('change', function () {
      if (batchSelector.val() != 0 || batchSelector.val() != '') {
        $('#batch-copy-move').show();
      } else {
        $('#batch-copy-move').hide();
      }
    });
  }
});
