<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Modules
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the Modules component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @since 1.6
 */
class ModulesViewModule extends JView
{
	public $params;
	public $positions;

	function display($tpl = null)
	{
		// Initialize some variables
		$db 	=& JFactory::getDBO();
		$user 	=& JFactory::getUser();

		$module = JRequest::getVar('module', '', '', 'cmd');

		$row		= &$this->get('data');
		$client		= &$this->get('client');
		$positions	= &$this->get('positions');
		$isNew		= ($row->id < 1);

		JToolBarHelper::title(JText::_('Module') . ': <small><small>[ '. JText::_('Edit') .' ]</small></small>', 'module.png');

		if ($row->module == 'mod_custom') {
			JToolBarHelper::Preview('index.php?option=com_modules&tmpl=component&client='.$client->id.'&pollid='.$row->id);
		}
		JToolBarHelper::save();
		JToolBarHelper::apply();
		if (!$isNew) {
			// for existing items the button is renamed `close`
			JToolBarHelper::cancel('cancel', 'Close');
		} else {
			JToolBarHelper::cancel();
		}
		JToolBarHelper::help('screen.modules.edit');

		$lists 	= array();

		$row->content = htmlspecialchars(str_replace('&amp;', '&', $row->content), ENT_COMPAT, 'UTF-8');
		JFilterOutput::objectHTMLSafe($row, ENT_QUOTES, array('content', 'params'));

		// Edit or Create?
		if ($isNew) {
			$row->position 	= 'left';
			$row->showtitle = true;
			$row->published = 1;
			//$row->ordering = $l;

			$row->module 	= $module;
		}

		if ($client->id == 1)
		{
			$where 				= 'client_id = 1';
			$lists['client_id'] = 1;
			$path				= 'mod1_xml';
		}
		else
		{
			$where 				= 'client_id = 0';
			$lists['client_id'] = 0;
			$path				= 'mod0_xml';
		}

		$query = 'SELECT position, ordering, showtitle, title'
		. ' FROM #__modules'
		. ' WHERE '. $where
		. ' ORDER BY ordering'
		;
		$db->setQuery($query);
		if (!($orders = $db->loadObjectList())) {
			echo $db->stderr();
			return false;
		}

		$orders2 	= array();

		$l = 0;
		$r = 0;
		for ($i=0, $n=count($orders); $i < $n; $i++) {
			$ord = 0;
			if (array_key_exists($orders[$i]->position, $orders2)) {
				$ord =count(array_keys($orders2[$orders[$i]->position])) + 1;
			}

			$orders2[$orders[$i]->position][] = JHtml::_('select.option',  $ord, $ord.'::'.addslashes($orders[$i]->title));
		}

		// get selected pages for $lists['selections']
		if (!$isNew) {
			$row->pages = 'select';
			$query = 'SELECT menuid AS value'
			. ' FROM #__modules_menu'
			. ' WHERE moduleid = '.(int) $row->id
			;
			$db->setQuery($query);
			$lookup = $db->loadObjectList();
			$row->pages = 'select';
			if (empty($lookup)) {
				$lookup = array(JHtml::_('select.option', '-1'));
				$row->pages = 'none';
			} elseif (count($lookup) == 1 && $lookup[0]->value == 0) {
				$row->pages = 'all';
			} else {
				/*
				 * If any menu value is negative, make the type "deselect". This
				 * has the side-effect of hiding any corruption in the values
				 * (i.e. a mix of positive and negative).
				 */
				foreach ($lookup as $key => $modMenu) {
					if ($modMenu->value < 0) {
						$lookup[$key]->value = -$modMenu->value;
						$row->pages = 'deselect';
					}
				}
			}
		} else {
			$lookup = array(JHtml::_('select.option', 0, JText::_('All')));
			$row->pages = 'all';
		}

		if ($row->access == 99 || $row->client_id == 1 || $lists['client_id']) {
			$lists['showtitle'] = 'N/A <input type="hidden" name="showtitle" value="1" />';
			$lists['selections'] = 'N/A';
		} else {
			if ($client->id == '1') {
				$lists['selections'] = 'N/A';
			} else {

				$selections = JHtml::_('menu.linkoptions');
				$lists['selections'] = JHtml::_(
					'select.genericlist',
					$selections,
					'selections[]',
					'class="inputbox" size="15" multiple="multiple"',
					'value',
					'text',
					$lookup,
					'selections'
				);
			}
			$lists['showtitle'] = JHtml::_(
				'select.booleanlist',
				'showtitle',
				'class="inputbox"',
				$row->showtitle
			);
		}

		// build the html select list for published
		$lists['published'] = JHtml::_(
			'select.booleanlist',
			'published',
			'class="inputbox"',
			$row->published
		);

		$row->description = '';

		$lang =& JFactory::getLanguage();
		if ($client->id != '1') {
			$lang->load(trim($row->module), JPATH_SITE);
			$lang->load('joomla', JPATH_SITE.DS.'modules'.DS.trim($row->module));
		} else {
			$lang->load(trim($row->module));
			$lang->load('joomla', JPATH_ADMINISTRATOR.DS.'modules'.DS.trim($row->module));
		}

		// xml file for module
		if ($row->module == 'mod_custom') {
			$xmlfile = JApplicationHelper::getPath($path, 'mod_custom');
		} else {
			$xmlfile = JApplicationHelper::getPath($path, $row->module);
		}

		$data = JApplicationHelper::parseXMLInstallFile($xmlfile);
		if ($data)
		{
			foreach($data as $key => $value) {
				$row->$key = $value;
			}
		}

		// get params definitions
		$params = new JParameter($row->params, $xmlfile, 'module');
		// Check for component metadata.xml file
		//$path = JApplicationHelper::getPath('mod'.$client->id.'_xml', $row->module);
		//$params = new JParameter($row->params, $path);
		//$document =& JFactory::getDocument();

		$this->assignRef('lists',		$lists);
		$this->assignRef('row',			$row);
		$this->assignRef('orders2',		$orders2);
		$this->assignRef('client',		$client);
		$this->assignRef('params',		$params);
		$this->assignRef('positions',	$positions);

		parent::display($tpl);
	}
}
