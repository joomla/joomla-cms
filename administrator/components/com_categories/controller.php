<?php
/**
 * @version	 $Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Categories view class for the Category package.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @since		1.6
 */
class CategoriesController extends JController
{
	/**
	 * Method to display a view.
	 *
	 * @return	void
	 */
	function display()
	{
		// Get the document object.
		$document = &JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'categories');
		$vFormat	= $document->getType();
		$lName		= JRequest::getWord('layout', 'default');

		// Get and render the view.
		if ($view = &$this->getView($vName, $vFormat))
		{
			// Get the model for the view.
			$model = &$this->getModel($vName);

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);

			$this->addLinkbar($model);
			$view->display();
		}
	}

	/**
	 * Configure the Linkbar
	 *
	 * @param	string	The name of the active view
	 */
	public function addLinkbar(&$model)
	{
		// This is pretty interesting :)
		$extension = $model->getState('filter.extension');

		if ($extension == 'com_categories') {
			return;
		}

		$file = JPath::clean(JPATH_ADMINISTRATOR.'/components/'.$extension.'/controller.php');
		if (file_exists($file))
		{
			require_once $file;
			$prefix	= ucfirst(str_replace('com_', '', $extension));
			$cName	= $prefix.'Controller';
			if (class_exists($cName))
			{
				$controller = new $cName;
				if ($controller instanceof JController && method_exists($controller, 'addLinkbar'))
				{
					$lang = &JFactory::getLanguage();
					$lang->load($extension);
					$controller->addLinkbar('categories');
				}
			}
		}
	}
}
