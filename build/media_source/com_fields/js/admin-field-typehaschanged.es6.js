/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
((Joomla) => {
  'use strict';

  Joomla.typeHasChanged = (element) => {
    // Display the loading indication
    document.body.appendChild(document.createElement('joomla-core-loader'));
    document.querySelector('input[name=task]').value = 'field.reload';
    element.form.submit();
  };
})(Joomla);
