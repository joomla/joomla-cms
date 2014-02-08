<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The AJAX view JSON format
 *
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @since   3.2
 */
class AjaxViewAjaxJson extends JViewBase
{

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @throws  RuntimeException
	 */
	function render()
	{
		$app = JFactory::getApplication();
		// Retrieve the data
		$data = $this->model->getData();
		// Return as JSON
		return new JResponseJson($data, null, false, $app->input->get('ignoreMessages', true, 'bool'));
	}
}
