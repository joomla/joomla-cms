/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/
var _this = this;

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// @todo vanillify this script
window.jQuery(document).ready(function ($) {
  'use strict';

  var propagate = function propagate() {
    var $this = $(_this);
    var sub = $this.closest('li').find('.treeselect-sub [type="checkbox"]');
    sub.prop('checked', _this.checked);
    if ($this.val() === 1) {
      sub.each(propagate);
    } else {
      sub.attr('disabled', _this.checked ? 'disabled' : null);
    }
  };

  $('.treeselect').on('click', '[type="checkbox"]', propagate).find('[type="checkbox"]:checked').each(propagate);
});
