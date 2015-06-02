<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Menus Menu Item TYpes View.
 *
 * @since  1.6
 */
class MenusViewMenutypes extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$input = JFactory::getApplication()->input;
		$this->recordId = $input->getInt('recordId');
		$types = $this->get('TypeOptions');

		// Adding System Links
		$list = array();
		$o = new JObject;
		$o->title = 'COM_MENUS_TYPE_EXTERNAL_URL';
		$o->type = 'url';
		$o->description  = 'COM_MENUS_TYPE_EXTERNAL_URL_DESC';
		$o->request = null;
		$list[] = $o;

		$o = new JObject;
		$o->title = 'COM_MENUS_TYPE_ALIAS';
		$o->type = 'alias';
		$o->description = 'COM_MENUS_TYPE_ALIAS_DESC';
		$o->request = null;
		$list[] = $o;

		$o = new JObject;
		$o->title = 'COM_MENUS_TYPE_SEPARATOR';
		$o->type = 'separator';
		$o->description = 'COM_MENUS_TYPE_SEPARATOR_DESC';
		$o->request = null;
		$list[] = $o;

		$o = new JObject;
		$o->title = 'COM_MENUS_TYPE_HEADING';
		$o->type = 'heading';
		$o->description = 'COM_MENUS_TYPE_HEADING_DESC';
		$o->request = null;
		$list[] = $o;
		$types['COM_MENUS_TYPE_SYSTEM'] = $list;

		$sortedTypes = array();

		foreach ($types as $name => $list)
		{
			$tmp = array();

			foreach ($list as $item)
			{
				$tmp[JText::_($item->title)] = $item;
			}

			ksort($tmp);
			$sortedTypes[JText::_($name)] = $tmp;
		}

		ksort($sortedTypes);

		$this->types = $sortedTypes;

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function addToolbar()
	{
		// Add page title
		JToolbarHelper::title(JText::_('COM_MENUS'), 'list menumgr');

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		// Cancel
		$title = JText::_('JTOOLBAR_CANCEL');
		$dhtml = "<button onClick=\"location.href='index.php?option=com_menus&view=items'\" class=\"btn\">
					<span class=\"icon-remove\" title=\"$title\"></span>
					$title</button>";
		$bar->appendButton('Custom', $dhtml, 'new');
	}
}
