<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content categories view.
 *
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 * @since       1.5
 */
class WeblinksViewCategories extends JViewCategory
{

	protected $item = null;

	/*
	 * @var  string  Default title to use for page title
	 */
	protected $defaultPageTitle = 'COM_WEBLINKS_DEFAULT_PAGE_TITLE';

	/*
	 * @var string  The name of the extension for the category
	*/
	protected $extension = 'com_weblinks';

	/*
	 * @var string  The name of the view to link individual items to
	*/
	protected $viewName = 'weblink';

	/**
	 * Display the view
	 *
	 * @return  mixed  False on error, null otherwise.
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

		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->maxLevelcat = $params->get('maxLevelcat', -1);
		$this->params = &$params;
		$this->parent = &$parent;
		$this->items  = &$items;

		$this->_prepareDocument();

		parent::display($tpl);
	}
	/**
	 * Method to prepares the document
	 *
	 * @since 3.1.3
	 */
	protected function _prepareDocument()
	{
		parent::_prepareDocument;
		parent::addFeed();
	}

}
