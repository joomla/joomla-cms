<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * HTML Grid Helper
 *
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 */
class JHtmlAclAdmin
{
	function enabled($value, $i)
	{
		$images	= array(0 => 'images/publish_x.png', 1 => 'images/tick.png');
		$alts	= array(0 => 'Disabled', 1 => 'Enabled');
		$img 	= JArrayHelper::getValue($images, $value, $images[0]);
		$task 	= $value == 1 ? 'acl.disable' : 'acl.enable';
		$alt 	= JArrayHelper::getValue($alts, $value, $images[0]);
		$action = JText::_('Click to toggle setting');

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">
		<img src="'. $img .'" border="0" alt="'. $alt .'" /></a>'
		;

		return $href;
	}

	function allowed($value, $i)
	{
		$images	= array(0 => 'images/publish_x.png', 1 => 'images/tick.png');
		$alts	= array(0 => 'Denied', 1 => 'Allowed');
		$img 	= JArrayHelper::getValue($images, $value, $images[0]);
		$task 	= $value == 1 ? 'acl.deny' : 'acl.allow';
		$alt 	= JArrayHelper::getValue($alts, $value, $images[0]);
		$action = JText::_('Click to toggle setting');

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">
		<img src="'. $img .'" border="0" alt="'. $alt .'" /></a>'
		;

		return $href;
	}

	/**
	 * Build the select list for access level
	 */
	function groups($selected = null, $parentId = 0, $type = 'aro')
	{
		$model = JModel::getInstance('Groups', 'AccessModel', array('ignore_request' => 1));
		$model->setState('list.group_type', $type);

		// Set the model state to get the groups tree
		$model->setState('list.select',		'a.id AS value, a.name AS text');
		$model->setState('list.tree',		1);
		$model->setState('list.order',		'a.lft');
		$model->setState('list.parent_id',	$parentId);
		// Get a list without resolving foreign keys
		$options = $model->getList(false);

		// Find the level of the parent
		$parentLevel = ($parentId > 0) ? $model->getLevel($parentId, $type) : 0;

		// Pad out the options to create a visual tree
		foreach ($options as $i => $option) {
			$options[$i]->text = str_pad($option->text, strlen($option->text) + 2*($option->level - $parentLevel), '- ', STR_PAD_LEFT);
		}
		//array_unshift($options, JHtml::_('select.option', 0, 'Select Group'));

		return JHtml::_('select.options', $options, 'value', 'text', $selected);
	}

	/**
	 * Display a select list of permitted ACL types
	 *
	 * @param	string $name		The name of the field
	 * @param	string $selected	The value of the selected option
	 * @param	string $attribs		Additional attributes to add to the select field
	 * @return	string				The required HTML for the SELECT tag
	 */
	function types($name, $selected, $attribs = 'class="inputbox"')
	{
		$options = array(
			JHtml::_('select.option', 1, JText::_('Acl Rule Type 1')),
			JHtml::_('select.option', 2, JText::_('Acl Rule Type 2')),
			JHtml::_('select.option', 3, JText::_('Acl Rule Type 3'))
		);

		return JHTML::_( 'select.genericlist', $options, $name, $attribs, 'value', 'text', $selected );
	}

	/**
	 * Displays a list of the available ACL sections
	 *
	 * @param	string $name		The name of the field
	 * @param	string $selected	The value of the selected option
	 * @param	string $attribs		Additional attributes to add to the select field
	 * @param	int $ruleType		The ACL rule type
	 * @return	string				The required HTML for the SELECT tag
	 */
	public static function sections($name, $selected, $attribs = '', $ruleType = 1)
	{
		$options = array(
			JHtml::_('select.option', '*', JText::_('All Sections'))
		);
		$db = &JFactory::getDbo();
		$db->setQuery(
			'SELECT s.value, s.name AS text, COUNT(acl.id) AS rule_count'
			.' FROM #__core_acl_acl_sections AS s'
			.' LEFT JOIN #__core_acl_acl AS acl ON acl.section_value = s.value AND acl.acl_type = '.(int) $ruleType
			.' WHERE s.hidden = 0'
			.' AND s.value IN ('
			.'  SELECT DISTINCT aco.section_value FROM #__core_acl_aco AS aco WHERE aco.acl_type = '.(int) $ruleType
			.' )'
			.' GROUP BY s.id'
			.' ORDER BY s.order_value, s.name'
		);
		$options = array_merge($options, $db->loadObjectList());
		foreach ($options as $i => $option) {
			// @todo Put into language string
			if ($option->value != '*') {
				$options[$i]->text = sprintf('%s (%d)', $option->text, $option->rule_count);
			}
		}
		return JHTML::_('select.genericlist', $options, $name, $attribs, 'value', 'text', $selected);
	}

	/**
	 * Displays a formatted list of the Actions in a References object
	 *
	 * @param JAclReferences $references
	 */
	function actionslist($references)
	{
		$html = '';
		foreach ($references->getAcos(true) as $section => $axos) {
			$html .= '<strong>'.$section.'</strong>';
			if (count($axos)) {
				$html .= '<ol>';
				foreach ($axos as $name) {
					$html .= '<li>'.$name.'</li>';
				}
				$html .= '</ol>';
			}
		}
		return $html;
	}

	/**
	 * Displays a formatted list of the Users in a References object
	 *
	 * @param JAclReferences $references
	 */
	function userslist($references)
	{
		$html = '';
		foreach ($references->getAros(true) as $section => $aros) {
			$html .= '<strong>'.$section.'</strong>';
			if (count($aros)) {
				$html .= '<ol>';
				foreach ($aros as $name) {
					$html .= '<li>'.$name.'</li>';
				}
				$html .= '</ol>';
			}
		}
		return $html;
	}

	/**
	 * Displays a formatted list of the Assets in a References object
	 *
	 * @param JAclReferences $references
	 */
	function assetslist($references)
	{
		$html = '';
		foreach ($references->getAxos(true) as $section => $aros) {
			$html .= '<strong>'.$section.'</strong>';
			if (count($aros)) {
				$html .= '<ol>';
				foreach ($aros as $name) {
					$html .= '<li>'.$name.'</li>';
				}
				$html .= '</ol>';
			}
		}
		return $html;
	}

	/**
	 * Displays a formatted list of the User Groups in a References object
	 *
	 * @param JAclReferences $references
	 */
	function usergroupslist($references)
	{
		$html = '';
		if ($groups = $references->getAroGroups()) {
			$html .= '<ol>';
			foreach ($groups as $name) {
				$html .= '<li>'.$name.'</li>';
			}
			$html .= '</ol>';
		}
		return $html;
	}

	/**
	 * Displays a formatted list of the Asset Groups in a References object
	 *
	 * @param JAclReferences $references
	 */
	function assetgroupslist($references)
	{
		$html = '';
		if ($groups = $references->getAxoGroups()) {
			$html .= '<ol>';
			foreach ($groups as $name) {
				$html .= '<li>'.$name.'</li>';
			}
			$html .= '</ol>';
		}
		return $html;
	}

	function actions($items, $selected=array(), $showSection = false)
	{
		// Make sure there are items to render.
		if (!count($items)) {
			return null;
		}

		// Build the UL.
		$html = '<ul class="checklist scroll" style="height:280px">';

		foreach ($items as $item)
		{
			// Initialize some base values for the element.
			$elementId	= 'aco_'.$item->section_value.'_'.$item->value;
			$checked	= (in_array($item->id, $selected)) ? ' checked="checked"' : '';

			// Build the LI.
			$html .= '<li>';

			// The form field element.
			$html .= '<input type="checkbox" name="acos['.$item->section_value.'][]" value="'.$item->id.'" id="'.$elementId.'"'.$checked.' />';

			// The label for the form field.
			$html .= '<label for="'.$elementId.'">'.($showSection ? '('.$item->section_name.') ' : '').$item->name.'</label>';

			// Close the LI.
			$html .= '</li>';
		}

		// Close the UL.
		$html .= '</ul>';

		return $html;
	}

	function hiddenactions($items, $selected=array())
	{
		// Make sure there are items to render.
		if (!count($items)) {
			return null;
		}

		// Build the Html.
		$html = '';

		foreach ($items as $item) {
			if (in_array($item->id, $selected)) {
				// The form field element.
				$html .= '<input type="hidden" name="acos['.$item->section_value.'][]" value="'.$item->id.'" />';
			}
		}

		return $html;
	}

	function assetgroups($items, $selected=array())
	{
		// Make sure there are items to render.
		if (!count($items)) {
			return null;
		}

		// Build the UL.
		$html = '<ul class="checklist scroll" style="height:280px">';

		foreach ($items as $item)
		{
			// Initialize some base values for the element.
			$elementId	= 'axogroup_'.$item->value;
			$checked	= (in_array($item->id, $selected)) ? ' checked="checked"' : '';

			// Build the LI.
			$html .= '<li>';

			// The form field element.
			$html .= '<input type="checkbox" name="axo_groups[]" value="'.$item->id.'" id="'.$elementId.'"'.$checked.' />';

			// The label for the form field.
			$html .= '<label for="'.$elementId.'" style="padding-left:'.intval(($item->level)*15+4).'px">'.$item->name.'</label>';

			// Close the LI.
			$html .= '</li>';
		}

		// Close the UL.
		$html .= '</ul>';

		return $html;
	}

	function hiddenassetgroups($items, $selected=array())
	{
		// Make sure there are items to render.
		if (!count($items)) {
			return null;
		}

		// Build the Html.
		$html = '';

		foreach ($items as $item)
		{
			// Initialize some base values for the element.
			if (in_array($item->id, $selected)) {
				// The form field element.
				$html .= '<input type="hidden" name="axo_groups[]" value="'.$item->id.'" />';
			}
		}

		return $html;
	}

	function assets($items, $selected=array())
	{
		// Make sure there are items to render.
		if (!count($items)) {
			return null;
		}

		// Build the UL.
		$html = '<ul class="checklist scroll" style="height:280px">';

		foreach ($items as $item)
		{
			// Initialize some base values for the element.
			$elementId	= 'axo_'.$item->section_value.'_'.$item->value;
			$checked	= (in_array($item->id, $selected)) ? ' checked="checked"' : '';

			// Build the LI.
			$html .= '<li>';

			// The form field element.
			$html .= '<input type="checkbox" name="axos['.$item->section_value.'][]" value="'.$item->id.'" id="'.$elementId.'"'.$checked.' />';

			// The label for the form field.
			$html .= '<label for="'.$elementId.'">'.$item->name.'</label>';

			// The section name for the item.
			//$html .= '<p><small>'.$item->section_name.'</small></p>';

			// Close the LI.
			$html .= '</li>';
		}

		// Close the UL.
		$html .= '</ul>';

		return $html;
	}

	function usergroups($items, $selected=array())
	{
		// Make sure there are items to render.
		if (!count($items)) {
			return null;
		}

		// Build the UL.
		$html = '<ul class="checklist scroll" style="height:280px">';

		foreach ($items as $item)
		{
			// Initialize some base values for the element.
			$elementId	= 'arogroup_'.$item->value;
			$checked	= (in_array($item->id, $selected)) ? ' checked="checked"' : '';

			// Build the LI.
			$html .= '<li>';

			// The form field element.
			$html .= '<input type="checkbox" name="aro_groups[]" value="'.$item->id.'" id="'.$elementId.'"'.$checked.' />';

			// The label for the form field.
			$html .= '<label for="'.$elementId.'" style="padding-left:'.intval(($item->level-2)*15+4).'px">'.$item->name.'</label>';

			// Close the LI.
			$html .= '</li>';
		}

		// Close the UL.
		$html .= '</ul>';

		return $html;
	}

}