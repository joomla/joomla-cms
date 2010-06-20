<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.access.access');
jimport('joomla.form.formfield');

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
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'Rules';

	/**
	 * Method to get the field input markup.
	 *
	 * TODO: Add access check.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$section	= $this->element['section'] ? (string) $this->element['section'] : '';
		$component	= $this->element['component'] ? (string) $this->element['component'] : '';
		$assetField	= $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';

		// Get the actions for the asset.
		$actions = JAccess::getActions($component, $section);

		// Iterate over the children and add to the actions.
		foreach ($this->element->children() as $el) {
			if ($el->getName() == 'action') {
				$actions[] = (object) array(
					'name'			=> (string) $el['name'],
					'title'			=> (string) $el['title'],
					'description'	=> (string) $el['description']
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
			$assetId = $this->form->getValue($assetField);
		}

		if (!empty($component) && $section != 'component') {
			return JHtml::_('rules.assetFormWidget', $actions, $assetId, $assetId ? null : $component, $this->name, $this->id);
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
		$html[] = '			<span class="acl-action">'.JText::_('JACTION_USER_GROUP').'</span>';
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

				$html[] = '				<select name="'.$this->name.'['.$action->name.']['.$group->value.']" id="'.$this->id.'_'.$action->name.'_'.$group->value.'" title="'.JText::sprintf('JSELECT_ALLOW_DENY_GROUP', JText::_($action->title), trim($group->text)).'">';
				$html[] = '					<option value=""'.($rules->allow($action->name, $group->value) === null ? ' selected="selected"' : '').'>'.JText::_('JINHERIT_UNSET').'</option>';
				$html[] = '					<option value="0"'.($rules->allow($action->name, $group->value) === false ? ' selected="selected"' : '').'>'.JText::_('JDENY').'</option>';
				$html[] = '					<option value="1"'.($rules->allow($action->name, $group->value) === true ? ' selected="selected"' : '').'>'.JText::_('JALLOW').'</option>';
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
			$option->text = str_repeat('&#160;&#160;',$option->level).$option->text;
		}

		return $options;
	}
}
