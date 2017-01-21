<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View for the global configuration
 *
 * @since  3.2
 */
class ConfigViewConfigHtml extends ConfigViewCmsHtml
{
	/**
	 * The form object
	 *
	 * @var   JForm
	 * @since 3.2
	 */
	public $form;

	/**
	 * The data to be displayed in the form
	 *
	 * @var   array
	 * @since 3.2
	 */
	public $data;

	/**
	 * Is the current user a super administrator?
	 *
	 * @var   boolean
	 * @since 3.2
	 */
	protected $userIsSuperAdmin;

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
