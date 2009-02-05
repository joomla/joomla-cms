<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Content
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * HTML View class for the Articles component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @since 1.0
 */
class ContentViewArticle extends JView
{
	protected $lists;
	protected $row;
	protected $option;
	protected $params;
	protected $contentSection;
	protected $sectioncategories;

	function display($tpl = null)
	{
		global $mainframe;

		// Initialize variables
		$db				= & JFactory::getDBO();
		$user			= & JFactory::getUser();

		$cid			= JRequest::getVar('cid', array(0), '', 'array');
		JArrayHelper::toInteger($cid, array(0));
		$id				= JRequest::getVar('id', $cid[0], '', 'int');
		$option			= JRequest::getCmd('option');
		$nullDate		= $db->getNullDate();
		$contentSection	= '';
		$sectionid		= 0;
		$model	=& $this->getModel();

		//get the content
		$row	=& $this->get('data');
		$edit	= JRequest::getVar('edit',true);

		if ($edit) {
			$sectionid = $row->sectionid;
			if ($row->state < 0) {
				$mainframe->redirect('index.php?option=com_content', JText::_('You cannot edit an archived item'));
			}
		}

		// A sectionid of zero means grab from all sections
		/*
		 * Not used?
		if ($sectionid == 0) {
			$where = ' WHERE section NOT LIKE "%com_%"';
		} else {
			// Grab from the specific section
			$where = ' WHERE section = '. $db->Quote($sectionid);
		}
		 */

		/*
		 * If the item is checked out we cannot edit it... unless it was checked
		 * out by the current user.
		 */
			// fail if checked out not by 'me'
		if (JTable::isCheckedOut($user->get ('id'), $row->checked_out))
		{
			$msg = JText::sprintf('DESCBEINGEDITTED', JText::_('The item'), $row->title);
			$mainframe->redirect('index.php?option=com_content', $msg);
		}

		if ($edit)
		{
			$model->checkout($user->get('id'));

			if (trim($row->images)) {
				$row->images = explode("\n", $row->images);
			} else {
				$row->images = array ();
			}

			$query = 'SELECT name' .
					' FROM #__users'.
					' WHERE id = '. (int) $row->created_by;
			$db->setQuery($query);
			$row->creator = $db->loadResult();

			// test to reduce unneeded query
			if ($row->created_by == $row->modified_by) {
				$row->modifier = $row->creator;
			} else {
				$query = 'SELECT name' .
						' FROM #__users' .
						' WHERE id = '. (int) $row->modified_by;
				$db->setQuery($query);
				$row->modifier = $db->loadResult();
			}

			$query = 'SELECT COUNT(content_id)' .
					' FROM #__content_frontpage' .
					' WHERE content_id = '. (int) $row->id;
			$db->setQuery($query);
			$row->frontpage = $db->loadResult();
			if (!$row->frontpage) {
				$row->frontpage = 0;
			}
		}
		else
		{
			if (!$sectionid && JRequest::getInt('filter_sectionid')) {
				$sectionid =JRequest::getInt('filter_sectionid');
			}

			if (JRequest::getInt('catid'))
			{
				$row->catid	 = JRequest::getInt('catid');
				$category 	 = & JTable::getInstance('category');
				$category->load($row->catid);
				$sectionid = $category->section;
			} else {
				$row->catid = NULL;
			}
			$createdate =& JFactory::getDate();
			$row->sectionid = $sectionid;
			$row->version = 0;
			$row->state = 1;
			$row->ordering = 0;
			$row->images = array ();
			$row->publish_up = $createdate->toMySQL();
			$row->publish_down = JText::_('Never');
			$row->creator = '';
			$row->created = $createdate->toMySQL();
			$row->modified = $nullDate;
			$row->modifier = '';
			$row->frontpage = 0;
		}

		$javascript = "onchange=\"changeDynaList('catid', sectioncategories, document.adminForm.sectionid.options[document.adminForm.sectionid.selectedIndex].value, 0, 0);\"";

		$query = 'SELECT s.id, s.title' .
				' FROM #__sections AS s' .
				' ORDER BY s.ordering';
		$db->setQuery($query);

		$sections[] = JHtml::_('select.option', '-1', '- '.JText::_('Select Section').' -', 'id', 'title');
		$sections[] = JHtml::_('select.option', '0', JText::_('Uncategorized'), 'id', 'title');
		$sections = array_merge($sections, $db->loadObjectList());
		$lists['sectionid'] = JHtml::_('select.genericlist',  $sections, 'sectionid', 'class="inputbox" size="1" '.$javascript, 'id', 'title', intval($row->sectionid));

		foreach ($sections as $section)
		{
			$section_list[] = (int) $section->id;
			// get the type name - which is a special category
			if ($row->sectionid) {
				if ($section->id == $row->sectionid) {
					$contentSection = $section->title;
				}
			} else {
				if ($section->id == $sectionid) {
					$contentSection = $section->title;
				}
			}
		}

		$sectioncategories = array ();
		$sectioncategories[-1] = array ();
		$sectioncategories[-1][] = JHtml::_('select.option', '-1', JText::_('Select Category'), 'id', 'title');
		$section_list = implode('\', \'', $section_list);

		$query = 'SELECT id, title, section' .
				' FROM #__categories' .
				' WHERE section IN (\''.$section_list.'\')' .
				' ORDER BY ordering';
		$db->setQuery($query);
		$cat_list = $db->loadObjectList();

		// Uncategorized category mapped to uncategorized section
		$uncat = new stdClass();
		$uncat->id = 0;
		$uncat->title = JText::_('Uncategorized');
		$uncat->section = 0;
		$cat_list[] = $uncat;
		foreach ($sections as $section)
		{
			$sectioncategories[$section->id] = array ();
			$rows2 = array ();
			foreach ($cat_list as $cat)
			{
				if ($cat->section == $section->id) {
					$rows2[] = $cat;
				}
			}
			foreach ($rows2 as $row2) {
				$sectioncategories[$section->id][] = JHtml::_('select.option', $row2->id, $row2->title, 'id', 'title');
			}
		}
		$sectioncategories['-1'][] = JHtml::_('select.option', '-1', JText::_('Select Category'), 'id', 'title');
		$categories = array();
		foreach ($cat_list as $cat) {
			if($cat->section == $row->sectionid)
				$categories[] = $cat;
		}

		$categories[] = JHtml::_('select.option', '-1', JText::_('Select Category'), 'id', 'title');
		$lists['catid'] = JHtml::_('select.genericlist',  $categories, 'catid', 'class="inputbox" size="1"', 'id', 'title', intval($row->catid));

		// build the html select list for ordering
		$query = 'SELECT ordering AS value, title AS text' .
				' FROM #__content' .
				' WHERE catid = ' . (int) $row->catid .
				' AND state >= 0' .
				' ORDER BY ordering';
		if($edit)
			$lists['ordering'] = JHtml::_('list.specificordering', $row, $id, $query, 1);
		else
			$lists['ordering'] = JHtml::_('list.specificordering', $row, '', $query, 1);

		// build the html radio buttons for frontpage
		$lists['frontpage'] = JHtml::_('select.booleanlist', 'frontpage', '', $row->frontpage);

		// build the html radio buttons for published
		$lists['state'] = JHtml::_('select.booleanlist', 'state', '', $row->state);

		/*
		 * We need to unify the introtext and fulltext fields and have the
		 * fields separated by the {readmore} tag, so lets do that now.
		 */
		if (JString::strlen($row->fulltext) > 1) {
			$row->text = $row->introtext . "<hr id=\"system-readmore\" />" . $row->fulltext;
		} else {
			$row->text = $row->introtext;
		}

		// Parameters
		$file 	= JPATH_COMPONENT.DS.'models'.DS.'article.xml';
		$params = new JParameter('', $file);
		$active = (intval($row->created_by) ? intval($row->created_by) : $user->get('id'));
		$params->set('created_by', $active);
		$params->set('access', $row->access);
		$params->set('created_by_alias', $row->created_by_alias);
		$params->set('created', JHtml::_('date', $row->created, '%Y-%m-%d %H:%M:%S'));
		$params->set('publish_up', JHtml::_('date', $row->publish_up, '%Y-%m-%d %H:%M:%S'));
		$params->set('publish_down', $row->publish_down);
		if (JHtml::_('date', $row->publish_down, '%Y') <= 1969 || $row->publish_down == $db->getNullDate()) {
			$params->set('publish_down', JText::_('Never'));
		} else {
			$params->set('publish_down', JHtml::_('date', $row->publish_down, '%Y-%m-%d %H:%M:%S'));
		}

		$params->bind($row->attribs);

		// Metadata Group
		$params->set('description', $row->metadesc);
		$params->set('keywords', $row->metakey);
		$params->bind($row->metadata);

		$this->assignRef('lists',		$lists);
		$this->assignRef('row',			$row);
		$this->assignRef('option',			$option);
		$this->assignRef('params',			$params);
		$this->assignRef('contentSection',	$contentSection);
		$this->assignRef('sectioncategories',	$sectioncategories);

		parent::display($tpl);
	}

	function _validateDate($date)
	{
		$db =& JFactory::getDBO();

		if (JHtml::_('date', $date, '%Y') == 1969 || $date == $db->getNullDate()) {
			$newDate = JText::_('Never');
		} else {
			$newDate = JHtml::_('date', $date, '%Y-%m-%d %H:%M:%S');
		}

		return $newDate;
	}
}
