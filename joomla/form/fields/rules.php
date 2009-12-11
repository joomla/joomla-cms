<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldRules extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Rules';

	/**
	 * Method to get the field input.
	 *
	 * @return	string		The field input.
	 */
	protected function _getInput()
	{
		// TODO: Add access check.

		// Get relevant attributes from the field definition.
		$section = $this->_element->attributes('section') !== null ? $this->_element->attributes('section') : '';
		$component = $this->_element->attributes('component') !== null ? $this->_element->attributes('component') : '';
		$assetField = $this->_element->attributes('asset_field') !== null ? $this->_element->attributes('asset_field') : 'asset_id';

		// Get the actions for the asset.
		$access = JFactory::getACL();
		$actions = JAccess::getActions($component, $section);

		// Iterate over the children and add to the actions.
		foreach ($this->_element->children() as $e) {
			if ($e->name() == 'action') {
				$actions[] = (object) array(
					'name' => (string) $e->attributes('name'),
					'title' => (string) $e->attributes('title'),
					'description' => (string) $e->attributes('description')
				);
			}
		}

		// Get the rules for this asset.
		if ($section == 'component') {
			// Need to find the asset id by the name of the component.
			$db = JFactory::getDbo();
			$db->setQuery('SELECT id FROM #__assets WHERE name = '.$db->quote($component));
			$assetId = (int) $db->loadResult();
			if ($error = $db->getErrorMsg()) {
				JError::raiseNotice(500, $error);
			}
		} else {
			$assetId = $this->_form->getValue($assetField);
		}

		if (!empty($component) && $section != 'component') {
			return JHtml::_('rules.assetFormWidget', $actions, $assetId, $assetId ? null : $component, $this->inputName, $this->inputId);
		}

		$rules = JAccess::getAssetRules($assetId);

		// Get the available user groups.
		$groups = $this->_getUserGroups();

		// Build the form control.
		$html = array();

		// Open the table.
		$html[] = '<table id="acl-config">';

		// The table heading.
		$html[] = '	<thead>';
		$html[] = '	<tr>';
		$html[] = '		<th>';
		$html[] = '			<span class="acl-action">'.JText::_('JAction_User_Group').'</span>';
		$html[] = '		</th>';
		foreach ($actions as $action) {
			$html[] = '		<th>';
			$html[] = '			<span class="acl-action" title="'.JText::_($action->description).'">'.JText::_($action->title).'</span>';
			$html[] = '		</th>';
		}
		$html[] = '	</tr>';
		$html[] = '	</thead>';

		// The table body.
		$html[] = '	<tbody>';
		foreach ($groups as $group) {
			$html[] = '	<tr>';
			$html[] = '		<th class="acl-groups">';
			$html[] = '			'.$group->text;
			$html[] = '		</th>';
			foreach ($actions as $action) {
				$html[] = '		<td>';
				// TODO: Fix this inline style stuff...
				//$html[] = '			<fieldset class="access_rule">';

				$html[] = '				<select name="'.$this->inputName.'['.$action->name.']['.$group->value.']" id="'.$this->inputId.'_'.$action->name.'_'.$group->value.'">';
				$html[] = '					<option value=""'.($rules->allow($action->name, $group->value) === null ? ' selected="selected"' : '').'>'.JText::_($assetId == 1 ? 'JInherit_Unset' : 'JInherit').'</option>';
				$html[] = '					<option value="0"'.($rules->allow($action->name, $group->value) === false ? ' selected="selected"' : '').'>'.JText::_('JDeny').'</option>';
				$html[] = '					<option value="1"'.($rules->allow($action->name, $group->value) === true ? ' selected="selected"' : '').'>'.JText::_('JAllow').'</option>';
				$html[] = '				</select>';
				//$html[] = '			</fieldset>';
				$html[] = '		</td>';
			}
			$html[] = '	</tr>';
		}
		$html[] = '	</tbody>';

		// Close the table.
		$html[] = '</table>';

		return implode("\n", $html);
	}

	protected function _getUserGroups()
	{
		// Get a database object.
		$db = JFactory::getDBO();

		// Get the user groups from the database.
		$db->setQuery(
			'SELECT a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level' .
			' FROM #__usergroups AS a' .
			' LEFT JOIN `#__usergroups` AS b ON a.lft > b.lft AND a.rgt < b.rgt' .
			' GROUP BY a.id' .
			' ORDER BY a.lft ASC'
		);
		$options = $db->loadObjectList();

		// Pad the option text with spaces using depth level as a multiplier.
		foreach ($options as $option) {
			$option->text = str_repeat('&nbsp;&nbsp;',$option->level).$option->text;
		}

		return $options;
	}
}