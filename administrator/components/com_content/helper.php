<?php
/**
 * @version		$Id: helper.php 11646 2009-03-01 19:34:56Z ian $
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

/**
 * Content Component Helper
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @since 1.5
 */
class ContentHelper
{
	function saveContentPrep(&$row)
	{
		// Get submitted text from the request variables
		$text = JRequest::getVar('text', '', 'post', 'string', JREQUEST_ALLOWRAW);

		// Clean text for xhtml transitional compliance
		$text		= str_replace('<br>', '<br />', $text);

		// Search for the {readmore} tag and split the text up accordingly.
		$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
		$tagPos	= preg_match($pattern, $text);

		if ($tagPos == 0)
		{
			$row->introtext	= $text;
		} else
		{
			list($row->introtext, $row->fulltext) = preg_split($pattern, $text, 2);
		}

		// Filter settings
		jimport('joomla.application.component.helper');
		$config	= JComponentHelper::getParams('com_content');
		$user	= &JFactory::getUser();
		$gid	= $user->get('gid');

		$filterGroups	=  $config->get('filter_groups');

		// convert to array if one group selected
		if ((!is_array($filterGroups) && (int) $filterGroups > 0)) {
			$filterGroups = array($filterGroups);
		}

		if (is_array($filterGroups) && in_array($gid, $filterGroups))
		{
			$filterType		= $config->get('filter_type');
			$filterTags		= preg_split('#[,\s]+#', trim($config->get('filter_tags')));
			$filterAttrs	= preg_split('#[,\s]+#', trim($config->get('filter_attritbutes')));
			switch ($filterType)
			{
				case 'NH':
					$filter	= new JFilterInput();
					break;
				case 'WL':
					$filter	= new JFilterInput($filterTags, $filterAttrs, 0, 0, 0);  // turn off xss auto clean
					break;
				case 'BL':
				default:
					$filter	= new JFilterInput($filterTags, $filterAttrs, 1, 1);
					break;
			}
			$row->introtext	= $filter->clean($row->introtext);
			$row->fulltext	= $filter->clean($row->fulltext);
		} elseif (empty($filterGroups) && $gid != '25') { // no default filtering for super admin (gid=25)
			$filter = new JFilterInput(array(), array(), 1, 1);
			$row->introtext	= $filter->clean($row->introtext);
			$row->fulltext	= $filter->clean($row->fulltext);
		}
		return true;
	}

	/**
	* Function to reset Hit count of an article
	*
	*/
	function resetHits($redirect, $id)
	{
		global $mainframe;

		// Initialize variables
		$db	= & JFactory::getDbo();

		// Instantiate and load an article table
		$row = & JTable::getInstance('content');
		$row->Load($id);
		$row->hits = 0;
		$row->store();
		$row->checkin();

		$msg = JText::_('Successfully Reset Hit count');
		$mainframe->redirect('index.php?option=com_content&sectionid='.$redirect.'&task=edit&id='.$id, $msg);
	}

	function filterCategory($query, $active = NULL)
	{
		// Initialize variables
		$db	= & JFactory::getDbo();

		$categories[] = JHtml::_('select.option', '0', '- '.JText::_('Select Category').' -');
		$db->setQuery($query);
		$categories = array_merge($categories, $db->loadObjectList());

		$category = JHtml::_('select.genericlist',  $categories, 'catid', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $active);

		return $category;
	}

}
