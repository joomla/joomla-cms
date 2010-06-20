<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Extended Utility class for all HTML drawing classes.
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @since		1.6
 */
abstract class JHtmlRules
{
	/**
	 * Displays a list of the available access sections
	 *
	 * @param	string	The form field name.
	 * @param	string	The name of the selected section.
	 * @param	string	Additional attributes to add to the select field.
	 * @param	boolean	True to add "All Sections" option.
	 *
	 * @return	string	The required HTML for the SELECT tag.
	 */
	public static function assetFormWidget($actions, $assetId = null, $parent = null, $control = 'jform[rules]', $idPrefix = 'jform_rules')
	{
		// Load the behavior.
		self::_loadBehavior();

		// Load the behavior.
		$images = self::_getImagesArray();

		// Get the user groups.
		$groups = self::_getUserGroups();

		// Get the incoming inherited rules as well as the asset specific rules.
		$inheriting = JAccess::getAssetRules($parent ? $parent : self::_getParentAssetId($assetId), true);
		$inherited = JAccess::getAssetRules($assetId, true);
		$rules = JAccess::getAssetRules($assetId);


		$html = array();

		$html[] = '<div class="acl-options">';
		$html[] = '	<dl class="tabs">';
		$html[] = '		<dt><a href="#">'.JText::_('JLIB_HTML_ACCESS_SUMMARY').'</a></dt>';
		$html[] = '		<dd>';
		$html[] = '			<p>'.JText::_('JLIB_HTML_ACCESS_SUMMARY_DESC').'</p>';
		$html[] = '			<table class="aclsummary-table" summary="'.JText::_('JLIB_HTML_ACCESS_SUMMARY_DESC').'">';
		$html[] = '			<caption>'.JText::_('JLIB_HTML_ACCESS_SUMMARY_DESC_CAPTION').'</caption>';
		$html[] = '			<tr>';
		$html[] = '				<th class="col1 hidelabeltxt">'.JText::_('JGROUPS').'</th>';
		foreach ($actions as $i => $action)
		{
			$html[] = '				<th class="col'.($i+2).'">'.JText::_($action->title).'</th>';
		}
		$html[] = '			</tr>';

		foreach ($groups as $i => $group)
		{
			$html[] = '			<tr class="row'.($i%2).'">';
			$html[] = '				<td class="col1">'.$group->text.'</td>';
			foreach ($actions as $i => $action)
			{
				$html[] = '				<td class="col'.($i+2).'">'.($assetId ? ($inherited->allow($action->name, $group->identities) ? $images['allow'] : $images['deny']) : ($inheriting->allow($action->name, $group->identities) ? $images['allow'] : $images['deny'])).'</td>';
			}
			$html[] = '			</tr>';
		}

		$html[] = '		</table>';
		$html[] = '	</dd>';

		foreach ($actions as $action)
		{
			$actionTitle = JText::_($action->title);
			$actionDesc	= JText::_($action->description);
			$html[] = '		<dt><a href="#">'.$actionTitle.'</a></dt>';
			$html[] = '		<dd>';
			$html[] = '			<p>'.$actionDesc.'</p>';
			$html[] = '			<table class="aclmodify-table" summary="'.$actionDesc.'">';
			$html[] = '			<caption>'.JText::_('JLIB_HTML_ACCESS_MODIFY_DESC_CAPTION_ACL').' '.$actionTitle.' '.JText::_('JLIB_HTML_ACCESS_MODIFY_DESC_CAPTION_TABLE').'</caption>';
			$html[] = '			<tr>';
			$html[] = '				<th class="col1 hidelabeltxt">'.JText::_('JGROUPS').'</th>';
			$html[] = '				<th class="col2">'.JText::_('JINHERIT').'</th>';
			$html[] = '				<th class="col3 hidelabeltxt">'.JText::_('JMODIFY').'</th>';
			$html[] = '				<th class="col4">'.JText::_('JCURRENT').'</th>';
			$html[] = '			</tr>';

			foreach ($groups as $i => $group)
			{
				$selected = $rules->allow($action->name, $group->value);

				$html[] = '			<tr class="row'.($i%2).'">';
				$html[] = '				<td class="col1">'.$group->text.'</td>';
				$html[] = '				<td class="col2">'.($inheriting->allow($action->name, $group->identities) ? $images['allow-i'] : $images['deny-i']).'</td>';
				$html[] = '				<td class="col3">';
				$html[] = '					<select id="'.$idPrefix.'_'.$action->name.'_'.$group->value.'" class="inputbox" size="1" name="'.$control.'['.$action->name.']['.$group->value.']" title="'.JText::sprintf('JSELECT_ALLOW_DENY_GROUP', $actionTitle, $group->text).'">';
				$html[] = '						<option value=""'.($selected === null ? ' selected="selected"' : '').'>'.JText::_('JINHERIT').'</option>';
				$html[] = '						<option value="1"'.($selected === true ? ' selected="selected"' : '').'>'.JText::_('JALLOW').'</option>';
				$html[] = '						<option value="0"'.($selected === false ? ' selected="selected"' : '').'>'.JText::_('JDENY').'</option>';
				$html[] = '					</select>';
				$html[] = '				</td>';
				$html[] = '				<td class="col4">'.($assetId ? ($inherited->allow($action->name, $group->identities) ? $images['allow'] : $images['deny']) : ($inheriting->allow($action->name, $group->identities) ? $images['allow'] : $images['deny'])).'</td>';
				$html[] = '			</tr>';
			}

			$html[] = '		</table>';
			$html[] = '	</dd>';
		}

		$html[] = ' </dl>';

		// Build the footer with legend and special purpose buttons.
		$html[] = '	<div class="clr"></div>';
		$html[] = '	<ul class="acllegend fltlft">';
		$html[] = '		<li class="acl-allowed">'.JText::_('JALLOWED').'</li>';
		$html[] = '		<li class="acl-denied">'.JText::_('JDENIED').'</li>';
		$html[] = '	</ul>';
		$html[] = '	<ul class="acllegend fltrt">';
		$html[] = '		<li class="acl-editgroups"><a href="#">'.JText::_('JLIB_HTML_ACCESS_EDIT_GROUPS').'</a></li>';
		$html[] = '		<li class="acl-resetbtn"><a href="#">'.JText::_('JLIB_HTML_ACCESS_RESET_TO_INHERIT').'</a></li>';
		$html[] = '	</ul>';
		$html[] = '</div>';

		return implode("\n", $html);
	}

	protected static function _getParentAssetId($assetId)
	{
		// Get a database object.
		$db = JFactory::getDBO();

		// Get the user groups from the database.
		$db->setQuery(
			'SELECT parent_id' .
			' FROM #__assets' .
			' WHERE id = '.(int) $assetId
		);
		return (int) $db->loadResult();
	}

	protected static function _getUserGroups()
	{
		// Get a database object.
		$db = JFactory::getDBO();

		// Get the user groups from the database.
		$db->setQuery(
			'SELECT a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level' .
			' , GROUP_CONCAT(b.id SEPARATOR \',\') AS parents' .
			' FROM #__usergroups AS a' .
			' LEFT JOIN `#__usergroups` AS b ON a.lft > b.lft AND a.rgt < b.rgt' .
			' GROUP BY a.id' .
			' ORDER BY a.lft ASC'
		);
		$options = $db->loadObjectList();

		// Pre-compute additional values.
		foreach ($options as &$option)
		{
			// Pad the option text with spaces using depth level as a multiplier.
			//$option->text = str_repeat('&#160;&#160;',$option->level).$option->text;

			$option->identities = ($option->parents) ? explode(',', $option->parents.','.$option->value) : array($option->value);
		}

		return $options;
	}

	protected static function _loadBehavior()
	{
		JHTML::_('script','system/rules.js', false, true);
	}

	protected static function _getImagesArray()
	{
		$base = JURI::root(true);
		$images['allow-l'] = '<label class="icon-16-allow" title="'.JText::_('JALLOWED').'">'.JText::_('JALLOWED').'</label>';
		$images['deny-l'] = '<label class="icon-16-deny" title="'.JText::_('JDENIED').'">'.JText::_('JDENIED').'</label>';
		$images['allow'] = '<a class="icon-16-allow" title="'.JText::_('JALLOWED').'"> </a>';
		$images['deny'] = '<a class="icon-16-deny" title="'.JText::_('JDENIED').'"> </a>';
		$images['allow-i'] = '<a class="icon-16-allowinactive" title="'.JText::_('JALLOW_INHERITED').'"> </a>';
		$images['deny-i'] = '<a class="icon-16-denyinactive" title="'.JText::_('JDENY_INHERITED').'"> </a>';

		return $images;
	}
}
