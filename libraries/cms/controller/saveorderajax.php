<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Libraries
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Base Display Controller
 *
 * @package     Joomla.Libraries
 * @subpackage  controller
 * @since       3.2
*/

class JControllerSaveorderajax extends JControllerCms
{
	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 * @throws  RuntimeException
	 */
	public function execute()
	{
		parent::execute();

		// Get the input
		$pks = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		try
		{
			$model = $this->getModel();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			// @todo Convert this to use JResponseJson
			echo "1";
		}

		// Close the application
		$this->app->close();
	}
}
