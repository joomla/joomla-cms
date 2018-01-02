<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

use Joomla\CMS\Factory;

defined('JPATH_PLATFORM') or die;

/**
 * Categories view base class.
 *
 * @since  3.2
 */
class CategoriesView extends HtmlView
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

		$app = Factory::getApplication();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			$app->enqueueMessage($errors, 'error');

			return false;
		}

		if ($items === false || $parent === false)
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
		// Because the application sets a default page title, we need to get it from the menu item itself
		$menu = Factory::getApplication()->getMenu()->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', \JText::_($this->pageHeading));
		}

		$this->setDocumentHeadData();
	}
}
