<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Plugins component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Plugins
 * @since 1.0
 */
class PluginsViewPlugin extends JView
{
	function display($tpl = null)
	{
		global $option;

		$db		= &JFactory::getDbo();
		$user 	= &JFactory::getUser();

		$client = JRequest::getWord('client', 'site');
		$cid 	= JRequest::getVar('cid', array(0), '', 'array');
		JArrayHelper::toInteger($cid, array(0));

		$lists 	= array();
		$row 	= &JTable::getInstance('plugin');

		// load the row from the db table
		$row->load($cid[0]);

		// fail if checked out not by 'me'

		if ($row->isCheckedOut($user->get('id')))
		{
			$msg = JText::sprintf('DESCBEINGEDITTED', JText::_('The plugin'), $row->title);
			$this->setRedirect('index.php?option='. $option .'&client='. $client, $msg, 'error');
			return false;
		}

		if ($client == 'admin') {
			$where = "client_id='1'";
		} else {
			$where = "client_id='0'";
		}

		// get list of groups
		if ($row->access == 99 || $row->client_id == 1) {
			$lists['access'] = 'Administrator<input type="hidden" name="access" value="99" />';
		} else {
			// build the html select list for the group access
			$lists['access'] = JHtml::_('access.assetgroups', 'access', $row->access);
		}

		if ($cid[0])
		{
			$row->checkout($user->get('id'));

			if ($row->ordering > -10000 && $row->ordering < 10000)
			{
				// build the html select list for ordering
				$query = 'SELECT ordering AS value, name AS text'
					. ' FROM #__plugins'
					. ' WHERE folder = '.$db->Quote($row->folder)
					. ' AND published > 0'
					. ' AND '. $where
					. ' AND ordering > -10000'
					. ' AND ordering < 10000'
					. ' ORDER BY ordering'
				;
				$order = JHtml::_('list.genericordering',  $query);
				$lists['ordering'] = JHtml::_('select.genericlist',   $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval($row->ordering));
			} else {
				$lists['ordering'] = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. JText::_('This plugin cannot be reordered');
			}

			$lang = &JFactory::getLanguage();
			$lang->load('plg_' . trim($row->folder) . '_' . trim($row->element), JPATH_ADMINISTRATOR);

			$data = JApplicationHelper::parseXMLInstallFile(JPATH_SITE . DS . 'plugins'. DS .$row->folder . DS . $row->element .'.xml');

			$row->description = $data['description'];

		} else {
			$row->folder 		= '';
			$row->ordering 		= 999;
			$row->published 	= 1;
			$row->description 	= '';
		}

		$lists['published'] = JHtml::_('select.booleanlist',  'published', 'class="inputbox"', $row->published);

		// get params definitions
		$params = new JParameter($row->params, JApplicationHelper::getPath('plg_xml', $row->folder.DS.$row->element), 'plugin');

		$this->assignRef('lists',		$lists);
		$this->assignRef('plugin',		$row);
		$this->assignRef('params',		$params);

		parent::display($tpl);
	}
}