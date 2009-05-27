<?php
/**
 * @version		$Id: view.html.php 10498 2008-07-04 00:05:36Z ian $
 * @package		Joomla.Site
 * @subpackage	Weblinks
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	Weblinks
 * @since 1.0
 */
class WeblinksViewCategories extends JView
{
	public $state;
	public $items;
	public $pagination;

	function display($tpl = null)
	{
		$app		= &JFactory::getApplication();
		$params		= &$app->getParams();

		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Prepare the data.
		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$item		= &$items[$i];
			$item->slug	= $item->alias ? ($item->id.':'.$item->alias) : $item->id;

			// Prepare category description
			$item->description = JHtml::_('content.prepare', $item->description);
		}

		$this->assignRef('params',		$params);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);

		// Prepare the document.
		$menus	= &JSite::getMenu();

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		if ($menu = $menus->getActive())
		{
			$menuParams = new JParameter($menu->params);
			if ($title = $menuParams->get('page_title')) {
				$this->document->setTitle($title);
			}
			else {
				$this->document->setTitle(JText::_('Web Links'));
			}
		}
		else {
			$this->document->setTitle(JText::_('Web Links'));
		}

		parent::display($tpl);
	}
}
