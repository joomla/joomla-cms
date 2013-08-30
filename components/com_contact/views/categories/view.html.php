<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content categories view.
 *
 * @package     Joomla.Site
 * @subpackage  com_contact
 * @since       1.6
 */
class ContactViewCategories extends JViewLegacy
{
	protected $state = null;

	protected $item = null;

	protected $items = null;

	protected $pagination = null;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  Exception on failure, void on success.
	 *
	 * @since   1.5
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$parent		= $this->get('Parent');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));

			return false;
		}

		if ($items === false)
		{
			return JError::raiseError(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}

		if ($parent == false)
		{
			return JError::raiseError(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}

		$params = &$state->params;

		$items = array($parent->id => $items);

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		foreach ($items as $itemElement)
		{
			$itemElement->event = new stdClass;

			// For some plugins.
			!empty($itemElement->description)? $itemElement->text = $itemElement->description : $itemElement->text = null;

			$dispatcher = JEventDispatcher::getInstance();

			JPluginHelper::importPlugin('content');
			$dispatcher->trigger('onContentPrepare', array ('com_contact.categories', &$itemElement, &$itemElement->core_params, 0));

			$results = $dispatcher->trigger('onContentAfterTitle', array('com_contact.categories', &$itemElement, &$itemElement->core_params, 0));
			$itemElement->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_contact.categories', &$itemElement, &$itemElement->core_params, 0));
			$itemElement->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_contact.categories', &$itemElement, &$itemElement->core_params, 0));
			$itemElement->event->afterDisplayContent = trim(implode("\n", $results));

			if ($itemElement->text)
			{
				$itemElement->description = $itemElement->text;
			}
		}

		$this->maxLevelcat = $params->get('maxLevelcat', -1);
		$this->params = &$params;
		$this->parent = &$parent;
		$this->items = &$items;

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	protected function _prepareDocument()
	{
		$app	= JFactory::getApplication();
		$menus	= $app->getMenu();
		$title	= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->def('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_CONTACT_DEFAULT_PAGE_TITLE'));
		}
		$title = $this->params->get('page_title', '');
		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
