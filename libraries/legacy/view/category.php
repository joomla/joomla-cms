<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_categoriess
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Base HTML View class for the a Catgory list
 *
 * @package     Joomla.Libraries
 * @subpackage  View
 * @since       3.1
 */
class JViewCategory extends JViewLegacy
{
	protected $state;

	/**
	 * Category items data
	 *
	 * @var array
	 */
	protected $items;

	/*
	 * @var JModelCategory  The category model object for this category
	*/

	protected $category;

	/**
	 * The list of other categories for this extension.
	 *
	 * @var        array
	 */
	protected $categories;

	/**
	 * Pagination
	 *
	 * @var        JPagination
	 */

	protected $pagination;

	protected $children;

	/*
	 * @var string  The name of the extension for the category
	 */
	protected $extension;



	public function commonCategoryDisplay()
	{
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$params		= $app->getParams();

		// Get some data from the models
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$category	= $this->get('Category');
		$children	= $this->get('Children');
		$parent 	= $this->get('Parent');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		if ($category == false)
		{
			return JError::raiseError(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}

		if ($parent == false)
		{
			return JError::raiseError(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}

		// Check whether category access level allows access.
		$groups	= $user->getAuthorisedViewLevels();
		if (!in_array($category->access, $groups))
		{
			return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// Setup the category parameters.
		$cparams = $category->getParams();
		$category->params = clone($params);
		$category->params->merge($cparams);

		$children = array($category->id => $children);

		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$maxLevel = $params->get('maxLevel', -1);
			$this->maxLevel   = &$maxLevel;
			$this->state      = &$state;
			$this->items      = &$items;
			$this->category   = &$category;
			$this->children   = &$children;
			$this->params     = &$params;
			$this->parent     = &$parent;
			$this->pagination = &$pagination;
			$this->user       = &$user;

			// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match
		$active	= $app->getMenu()->getActive();
		if ((!$active) || ((strpos($active->link, 'view=category') === false) || (strpos($active->link, '&id=' . (string) $this->category->id) === false)))
		{
		if ($layout = $category->params->get('category_layout'))
		{
		$this->setLayout($layout);
		}
		}
		elseif (isset($active->query['layout']))
		{
		// We need to set the layout in case this is an alternative menu item (with an alternative layout)
			$this->setLayout($active->query['layout']);
		}

		$this->category->tags = new JHelperTags;
		$this->category->tags->getItemTags('com_newsfeeds.category', $this->category->id);

	}
	public function display($tpl = null)
	{
		$this->_prepareDocument();

		parent::display($tpl);
	}

}
