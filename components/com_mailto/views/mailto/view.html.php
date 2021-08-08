<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Class for Mail.
 *
 * @since  1.5
 */
class MailtoViewMailto extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.5
	 */
	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->link = urldecode(JFactory::getApplication()->input->get('link', '', 'BASE64'));

		return parent::display($tpl);
	}
}
