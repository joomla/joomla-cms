/**
  * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
  * @license    GNU General Public License version 2 or later; see LICENSE.txt
  */

/**
 * Add a keyboard event listener to the Select Template Style element.
 *
 * This script is meant to be loaded deferred. This means that it's non-blocking
 * (the browser can load it whenever) and it doesn't need an on DOMContentLoaded event handler
 * because the browser is guaranteed to execute it only after the DOM content has loaded, the
 * whole point of it being deferred.
 */

const elIframe = document.getElementById('module-position-select');
const elTemplateSelect = document.getElementById('jform_template_style_select');

elTemplateSelect.addEventListener('change', (event) => {
  elIframe.src = elIframe.src.substring(0, elIframe.src.indexOf('templateStyle=') + 14) + event.target.value;
});
