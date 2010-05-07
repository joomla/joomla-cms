<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML Article View class for the Weblinks component
 *
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @since		1.5
 */
class WeblinksViewForm extends JView
{
	protected $state;
	protected $item;

	public function display($tpl = null)
	{
		// Initialise variables.
		$app		= &JFactory::getApplication();
		$user		= &JFactory::getUser();

		// Get model data.
		$state	= $this->get('State');
		$item	= $this->get('Item');
		$form	= $this->get('Form');

		if (empty($item->id)) {
			$authorised = $user->authorise('core.create', 'com_weblinks');
		}
		else {
			$authorised = $user->authorise('core.edit', 'com_weblinks.weblink.'.$item->id);
		}

		if ($authorised !== true) {
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return false;
		}

		if (!empty($item)) {
			$form->bind($item);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		// Create a shortcut to the parameters.
		$params	= &$state->params;

		$this->assignRef('state',	$state);
		$this->assignRef('params',	$params);
		$this->assignRef('item',	$item);
		$this->assignRef('form',	$form);
		$this->assignRef('user',	$user);

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app	= &JFactory::getApplication();
		$menus	= &JSite::getMenu();
		$title	= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('COM_WEBLINKS_FORM_EDIT_WEBLINK'));
		}
		$title = $this->params->def('page_title', JText::_('COM_WEBLINKS_FORM_EDIT_WEBLINK'));
		if ($app->getCfg('sitename_pagetitles', 0)) {
			$title = JText::sprintf('JPAGETITLE', htmlspecialchars_decode($app->getCfg('sitename')), $title);
		}
		$this->document->setTitle($title);
	}
}
