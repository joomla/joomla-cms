<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Component Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 */
class ContentController extends JController
{
	/**
	 * Display the view
	 */
	function display()
	{
		// Get the document object.
		$document	= &JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getWord('view', 'articles');
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

			$this->addLinkbar($vName);
			$view->display();
		}
	}

	/**
	 * Configure the Linkbar
	 *
	 * @param	string	The name of the active view
	 */
	public function addLinkbar($vName)
	{
		JSubMenuHelper::addEntry(JText::_('Content_Link_Articles'),		'index.php?option=com_content&view=articles',			$vName == 'articles');
		JSubMenuHelper::addEntry(JText::_('Content_Link_Categories'),	'index.php?option=com_categories&extension=com_content',	$vName == 'categories');
		JSubMenuHelper::addEntry(JText::_('Content_Link_Featured'),		'index.php?option=com_content&view=featured',			$vName == 'featured');
	}
}