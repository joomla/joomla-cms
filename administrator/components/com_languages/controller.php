<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * Languages Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.5
 */
class LanguagesController extends JController
{
	/**
	 * task to display the view
	 */
	function display()
	{
		// Get the document object.
		$document = &JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'installed');
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

			$view->display();

			// Load the submenu.
			require_once JPATH_COMPONENT.DS.'helpers'.DS.'languages.php';
			LanguagesHelper::addSubmenu($vName);
		}
	}

	/**
	 * task to set the default language
	 */
	function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));
		$model = & $this->getModel('languages');
		if ($model->publish())
		{
			$msg = JText::_('Langs_Default_Language_Saved');
			$type = 'message';
		}
		else
		{
			$msg = & $this->getError();
			$type = 'error';
		}
		$client = & $model->getClient();
		$this->setredirect('index.php?option=com_languages&client='.$client->id,$msg,$type);
	}
}
