<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Menus Menu Item Types View.
 *
 * @since  1.6
 */
class MenusViewMenutypes extends JViewLegacy
{
	/**
	 * @var  JObject[]
	 */
	protected $types;

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
		$app            = JFactory::getApplication();
		$this->recordId = $app->input->getInt('recordId');

		$types = $this->get('TypeOptions');

		$this->addCustomTypes($types);

		$sortedTypes = array();

		foreach ($types as $name => $list)
		{
			$tmp = array();

			foreach ($list as $item)
			{
				$tmp[JText::_($item->title)] = $item;
			}

			uksort($tmp, 'strcasecmp');
			$sortedTypes[JText::_($name)] = $tmp;
		}

		uksort($sortedTypes, 'strcasecmp');

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
		$bar = JToolbar::getInstance('toolbar');

		// Cancel
		$title = JText::_('JTOOLBAR_CANCEL');
		$dhtml = "<button onClick=\"location.href='index.php?option=com_menus&view=items'\" class=\"btn\">
					<span class=\"icon-remove\" title=\"$title\"></span>
					$title</button>";
		$bar->appendButton('Custom', $dhtml, 'new');
	}

	/**
	 * Method to add system link types to the link types array
	 *
	 * @param   array  $types  The list of link types
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function addCustomTypes(&$types)
	{
		if (empty($types))
		{
			$types = array();
		}

		// Adding System Links
		$list           = array();
		$o              = new JObject;
		$o->title       = 'COM_MENUS_TYPE_EXTERNAL_URL';
		$o->type        = 'url';
		$o->description = 'COM_MENUS_TYPE_EXTERNAL_URL_DESC';
		$o->request     = null;
		$list[]         = $o;

		$o              = new JObject;
		$o->title       = 'COM_MENUS_TYPE_ALIAS';
		$o->type        = 'alias';
		$o->description = 'COM_MENUS_TYPE_ALIAS_DESC';
		$o->request     = null;
		$list[]         = $o;

		$o              = new JObject;
		$o->title       = 'COM_MENUS_TYPE_SEPARATOR';
		$o->type        = 'separator';
		$o->description = 'COM_MENUS_TYPE_SEPARATOR_DESC';
		$o->request     = null;
		$list[]         = $o;

		$o              = new JObject;
		$o->title       = 'COM_MENUS_TYPE_HEADING';
		$o->type        = 'heading';
		$o->description = 'COM_MENUS_TYPE_HEADING_DESC';
		$o->request     = null;
		$list[]         = $o;

		if ($this->get('state')->get('client_id') == 1)
		{
			$o              = new JObject;
			$o->title       = 'COM_MENUS_TYPE_CONTAINER';
			$o->type        = 'container';
			$o->description = 'COM_MENUS_TYPE_CONTAINER_DESC';
			$o->request     = null;
			$list[]         = $o;
		}

		$types['COM_MENUS_TYPE_SYSTEM'] = $list;
	}
}
