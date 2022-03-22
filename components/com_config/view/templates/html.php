<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit a template style.
 *
 * @since  3.2
 */
class ConfigViewTemplatesHtml extends ConfigViewCmsHtml
{
	public $item;

	public $form;

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.2
	 */
	public function render()
	{
		$user = JFactory::getUser();
		$this->userIsSuperAdmin = $user->authorise('core.admin');

		return parent::render();
	}
}
