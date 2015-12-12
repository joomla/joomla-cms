<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Modules manager master display controller.
 *
 * @since  1.6
 */
class ModulesController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean        $cachable   If true, the view output will be cached
	 * @param   array|boolean  $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}
	 *
	 * @return  JController    This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$id     = $this->input->getInt('id');

		$document = JFactory::getDocument();

		// For JSON requests
		if ($document->getType() == 'json')
		{

			$view = new ModulesViewModule;

			// Get/Create the model
			if ($model = new ModulesModelModule)
			{
				// Checkin table entry
				if (!$model->checkout($id))
				{
					JFactory::getApplication()->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'), 'error');
					return false;
				}

				// Push the model into the view (as default)
				$view->setModel($model, true);
			}

			$view->document = $document;

			return $view->display();
		}

		require_once JPATH_COMPONENT . '/helpers/modules.php';

		// Load the submenu.
		ModulesHelper::addSubmenu($this->input->get('view', 'modules'));

		return parent::display();
	}
}
