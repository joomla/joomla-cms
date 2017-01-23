<?php
/**
 * @package     Joomla.Cms
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Cms\View;

defined('JPATH_PLATFORM') or die;

/**
 * Categories view base class.
 *
 * @since  3.2
 */
class Categories extends View
{
	/**
	 * State data
	 *
	 * @var    \Joomla\Registry\Registry
	 * @since  3.2
	 */
	protected $state;

	/**
	 * Category items data
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected $items;

	/**
	 * Language key for default page heading
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $pageHeading;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   3.2
	 */
	public function display($tpl = null)
	{
		$state  = $this->get('State');
		$items  = $this->get('Items');
		$parent = $this->get('Parent');

		$app = \JFactory::getApplication();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage($errors, 'error');

			return false;
		}

		if ($items === false)
		{
			$app->enqueueMessage(\JText::_('JGLOBAL_CATEGORY_NOT_FOUND'), 'error');

			return false;
		}

		if ($parent == false)
		{
			$app->enqueueMessage(\JText::_('JGLOBAL_CATEGORY_NOT_FOUND'), 'error');

			return false;
		}

		$params = &$state->params;

		$items = array($parent->id => $items);

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'), ENT_COMPAT, 'UTF-8');

		$this->maxLevelcat = $params->get('maxLevelcat', -1) < 0 ? PHP_INT_MAX : $params->get('maxLevelcat', PHP_INT_MAX);
		$this->params      = &$params;
		$this->parent      = &$parent;
		$this->items       = &$items;

		$this->prepareDocument();

		return parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function prepareDocument()
	{
		$app   = \JFactory::getApplication();
		$menus = $app->getMenu();

		// Because the application sets a default page title, we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', \JText::_($this->pageHeading));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = \JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = \JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
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
