/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

let map;
/**
 * Resolve module name to it's URL when "importmap" is not supported,
 * otherwise return module name unchanged.
 *
 * Usage example:
 * const { foo } = await import(joomlaESMap('@bar-module'));
 * or
 * import(joomlaESMap('@bar-module')).then(({ foo }) => { ... });
 *
 * @param {String}   name  Module name
 * @param {boolean}  force Force to resolve, even when "importmap" is supported
 * @returns {String}
 */
window.joomlaESMap = (name, force) => {
  if (HTMLScriptElement.supports('importmap') && !force) return name;
  if (!map) {
    const m = document.querySelector('script[type="importmap"]');
    map = m ? JSON.parse(m.textContent) : {imports: {}};
  }
  return map.imports[name] || name;
};
