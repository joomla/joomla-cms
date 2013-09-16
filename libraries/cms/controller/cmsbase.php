<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Libraries
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
class JControllerCmsbase extends JControllerBase
{
	/*
	 * Prefix for the view and model classes
	 *
	 * @var  string
	 */
	public $prefix;

	/**
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Get the application
		$this->app = $this->getApplication();
		$this->app->redirect('index.php?option=' . $this->input->get('option', 'com_cpanel'));

		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JInvalid_Token'));

		$this->componentFolder = $this->input->getWord('option', 'com_content');
		$this->viewName     = $this->input->getWord('view', 'articles');

		return $this;

	}

	/**
	 * Method to check whether an ID is in the edit list.
	 *
	 * @param   string   $context  The context for the session storage.
	 * @param   integer  $id       The ID of the record to add to the edit list.
	 *
	 * @return  boolean  True if the ID is in the edit list.
	 *
	 * @since   12.2
	 */
	protected function checkEditId($context, $id)
	{
		if ($id)
		{
			$app = JFactory::getApplication();
			$values = (array) $app->getUserState($context . '.id');

			$result = in_array((int) $id, $values);

			if (defined('JDEBUG') && JDEBUG)
			{
				JLog::add(
				sprintf(
				'Checking edit ID %s.%s: %d %s',
				$context,
				$id,
				(int) $result,
				str_replace("\n", ' ', print_r($values, 1))
				),
				JLog::INFO,
				'controller'
						);
			}

			return $result;
		}
		else
		{
			// No id for a new item.
			return true;
		}
	}

}
