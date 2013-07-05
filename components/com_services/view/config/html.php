<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_services
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


/**
 * View for the global configuration
 *
 * @package     Joomla.Site
 * @subpackage  com_services
 * @since       3.2
 */
class ServicesViewConfigHtml extends JViewCms
{
	public $state;

	public $form;

	public $data;

	/**
	 * Method to display the view.
	 * 
	 * @param   string  $tpl          Layout
	 * 
	 * @return  void
	 *
	 */
	public function render()
	{

		try
		{
			$user = JFactory::getUser();
			$app   = JFactory::getApplication();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');
		}
		// Check for model errors.
		/* if ($errors = $this->get('Errors'))
		{
			$app->enqueueMessage(implode('<br />', $errors), 'error');
			
			return;

		} */

		$this->userIsSuperAdmin = $user->authorise('core.admin');

		return parent::render();
	}

}
