/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla.fieldsChangeContext = (context) => {
  'use strict';

  const regex = /([?;&])context[^&;]*[;&]?/;
  const url = window.location.href;
  const query = url.replace(regex, '$1').replace(/&$/, '');
  // eslint-disable-next-line
  window.location.href = (query.length > 2 ? query + '&' : '?') + (context ? 'context=' + context : '');
};
