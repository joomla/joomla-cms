/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla.submitbutton = (pressbutton) => {
  if (pressbutton === 'associations.purge') {
    // eslint-disable-next-line no-restricted-globals
    if (confirm(Joomla.Text._('COM_ASSOCIATIONS_PURGE_CONFIRM_PROMPT'))) {
      Joomla.submitform(pressbutton);
    } else {
      return false;
    }
  } else {
    Joomla.submitform(pressbutton);
  }

  return true;
};
