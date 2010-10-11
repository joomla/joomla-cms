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
		// Initialise some field attributes.
		$section = $this->element['section'] ? (string)$this->element['section'] : '';
		$component = $this->element['component'] ? (string)$this->element['component'] : '';
		$assetField = $this->element['asset_field'] ? (string)$this->element['asset_field'] : 'asset_id';

		// Get the actions for the asset.
		$actions = JAccess::getActions($component, $section);

		// Iterate over the children and add to the actions.
		foreach($this->element->children() as $el)
		{
			if ($el->getName() == 'action') 
			{
				$actions[] = (object) array(
					'name'			=> (string) $el['name'],
					'title'			=> (string) $el['title'],
					'description'	=> (string) $el['description']
				);
			}
		}

		// Get the explicit rules for this asset.
		if ($section == 'component') 
		{
			// Need to find the asset id by the name of the component.
			$db = JFactory::getDbo();
			$db->setQuery('SELECT id FROM #__assets WHERE name = ' . $db->quote($component));
			$assetId = (int)$db->loadResult();

			if ($error = $db->getErrorMsg()) 
			{
				JError::raiseNotice(500, $error);
			}
		}
		else 
		{
			$assetId = $this->form->getValue($assetField);
		}

		if (!empty($component) && $section != 'component') 
		{
			return JHtml::_('rules.assetFormWidget', $actions, $assetId, $assetId ? null : $component, $this->name, $this->id);
		}

		$rules = JAccess::getAssetRules($assetId);

		// Get the global rules
		// Assumes the root is the first record which might not be safe.
		// TODO: Better to look at the "parent" - will need to do this when looking at refactoring the wdiget
		$globalRules = JAccess::getAssetRules(1);

		// Get the available user groups.
		$groups = $this->_getUserGroups();

		// Build the form control.
		$curLevel = 0;

		// Prepare output
		$html = array();
		$html[] = '<div id="permissions-sliders" class="pane-sliders">';
		$html[] = '<ul id="rules">';

		foreach($groups as $group)
		{
			$difLevel = $group->level - $curLevel;
			if ($difLevel > 0) 
			{
				$html[] = '<ul>';
			}
			else if ($difLevel < 0) 
			{
				$html[] = str_repeat('</li></ul>', -$difLevel);
			}
			$html[] = '<li>';

			$html[] = '<div class="panel">';
			$html[] = '<h3 class="jpane-toggler title" ><a href="javascript:void(0);"><span>';
			$html[] = str_repeat('<span class="level">|&ndash;</span> ', $curLevel = $group->level) . $group->text;
			$html[] = '</span></a></h3>';
			$html[] = '<div class="jpane-slider content">';
			$html[] = '<div class="mypanel">';
			$html[] = '<table class="group-rules">';
			$html[] = '<caption>' . JText::sprintf('JGROUP', $group->text) . '<br /><span>' . JText::_('JACTION_CONFIG_DESC') . '</span></caption>';
			$html[] = '<thead>';
			$html[] = '<tr>';
			$html[] = '<th class="actions" id="actions-th' . $group->value . '">';
			$html[] = '<span class="acl-action">' . JText::_('JACTION_USER_GROUP') . '</span>';
			$html[] = '</th>';
			$html[] = '<th class="settings" id="settings-th' . $group->value . '">';

			if ($component != '') 
			{
				$html[] = '<span class="acl-action">' . JText::_('JACTION_COMPONENT_SETTINGS') . '</span></th>';
				$html[] = '<th class="global-settings" id="global_th' . $group->value . '">';
				$html[] = '<span class="acl-action">' . JText::_('JACTION_GLOBAL_SETTINGS') . '</span>';
				$html[] = '</th>';
			}
			else 
			{
				$html[] = '<span class="acl-action">' . JText::_('JACTION_SELECT_SETTINGS') . '</span>';
				$html[] = '</th>';
				$html[] = '<th id="aclactionth' . $group->value . '">';
				$html[] = '<span class="acl-action">' . JText::_('JACTION_CURRENT_SETTINGS') . '</span>';
				$html[] = '</th>';
			}

			$html[] = '</tr>';
			$html[] = '</thead>';
			$html[] = '<tbody >';

			foreach ($actions as $action)
			{
				$html[] = '<tr>';
				$html[] = '<td headers="actions-th' . $group->value . '">';
				$html[] = '<label for="' . $this->id . '_' . $action->name . '_' . $group->value . '">';
				$html[] = JText::_($action->title);
				$html[] = '</label>';
				$html[] = '</td>';
				$html[] = '<td headers="settings-th' . $group->value . '">';

				$html[] = '<select name="' . $this->name . '[' . $action->name . '][' . $group->value . ']" id="' . $this->id . '_' . $action->name . '_' . $group->value . '" title="' . JText::sprintf('JSELECT_ALLOW_DENY_GROUP', JText::_($action->title), trim($group->text)) . '">';

				$groupaccess = JAccess::checkGroup($group->value, $action->name,$assetId);
				$globalrule = $globalRules->allow($action->name, $group->value);
				$rule = $rules->allow($action->name, $group->value);

				// Build the dropdowns for the permissions sliders
				// Don't do this for groups with global admin since they are allowed everything.
				// Check whether this is a component or global. If it is component use the asset rules.
				if ($component != '') 
				{
					if ($globalRules->allow('core.admin', $group->value) !== true) 
					{
						// 'Not Allowed' if nothing else is specified. Not saved in the database. Can be changed to 'Allowed' or 'Forbidden'.
						$html[] = '<option value=""' . (( $rule === null) ? ' selected="selected"' : '') . '>' . JText::_('JRULE_INHERITED') . '</option>';
						$html[] = '<option value="1"' . (( $rule === true) ? ' selected="selected"' : '') . '>' . JText::_('JRULE_ALLOWED') . '</option>';
						$html[] = '<option value="0"' . (($rule === false)  ? ' selected="selected"' : '') . '>' . JText::_('JRULE_FORBIDDEN') . '</option>';
					}
					else 
					{
						// Just the core.admin groups. These work the same whether in global configuration or a component configuration.
						// Groups with global admin permission always have allow on every other action
						$html[] = '<option value=""' .(( $rule === null) ? ' selected="selected"' : '') . '>' . JText::_('JRULE_INHERITED') . '</option>';
						$html[] = '<option value="1"' . ((($rule === true)) ? ' selected="selected"' : '') . '>' . JText::_('JRULE_ALLOWED') . '</option>';
						$html[] = '<option value="0"'.(( $rule === false) ? ' selected="selected"' : '') .'>' . JText::_('JRULE_FORBIDDEN') . '</option>';
					}
				}
				else 
				{
					// If it global config we need to handle a little differently.
					// Groups with global core.admin permissions inherit allow from that.
					if (JAccess::checkGroup($group->value, 'core.admin') !== true) 
					{
						// Soft deny if nothing else is specified. Not saved in the database. Can be changed to Allow or Deny

						$html[] = '<option value=""' . (( $globalrule === null) ? ' selected="selected"' : '') . '>' . JText::_('JRULE_INHERITED') . '</option>';
						$html[] = '<option value="0"' . (( $globalrule === false) ? ' selected="selected"' : '') . '>' . JText::_('JRULE_FORBIDDEN') . '</option>';
						$html[] = '<option value="1"' . (($globalrule === true) ? ' selected="selected"' : '') . '>' . JText::_('JRULE_ALLOWED') . '</option>';
					}
					else 
					{
						//Just the core.admin groups. These work the same whether in global configuration or a component configuration.
						//Groups with global admin permission always have allow on every other action
						if ($action->name === 'core.admin') 
						{
							$html[] = '<option value=""' . (( $globalrule == null) ? ' selected="selected"' : '') . '>' . JText::_('JRULE_INHERITED') . '</option>';
							$html[] = '<option value="1"' . ( $globalrule == true ? ' selected="selected"' : '') . '>' . JText::_('JRULE_ALLOWED') . '</option>';
							$html[] = '<option value="0"' . (( $globalrule === false) ? ' selected="selected"' : '') . '>' . JText::_('JRULE_FORBIDDEN') . '</option>';
						}
						else 
						{
							$html[] = '<option value=""' . (($globalrule === null) ? ' selected="selected"' : '') . '>' . JText::_('JRULE_INHERITED') . '</option>';
							$html[] = '<option value="1"' . ((( $globalrule === true)) ? ' selected="selected"' : '') . '>' . JText::_('JRULE_ALLOWED') . '</option>';
							$html[] = '<option value="0"' . (( $globalrule === false) ? ' selected="selected"' : '') . '>' . JText::_('JRULE_FORBIDDEN') . '</option>';
						}
					}
				}

				$html[] = '</select>&nbsp; ';
				if (($rule === true) && ($groupaccess === false))
				{
					$html[] = JText::_('JRULE_CONFLICT');  
				}
				$html[] = '</td>';
				$html[] = '<td headers="global_th' . $group->value . '">';

				// This is where we show the current effective settings considering currrent group, path and cascade.
				// Check whether this is a component or global. Change the text slightly.
				if ($component != '') 
				{
					if (JAccess::checkGroup($group->value, 'core.admin') !== true) 
					{
						if ( $groupaccess === null) 
						{
							$html[] = JText::_('JRULE_NOT_ALLOWED');
						}
						else if ( $groupaccess === false) 
						{
							$html[] = JText::_('JRULE_FORBIDDEN');
												
						}
						else if ($groupaccess === true) 
						{
							$html[] = JText::_('JRULE_ALLOWED');
						}

						//Now handle the groups with core.admin who always inherit an allow.
					} else 
					{
							$html[] = JText::_('JRULE_ALLOWED_ADMIN');
					}
				}
				else 
				{
					// Global configuration actions.
					// Handle groups that do not have global admin.
					if (JAccess::checkGroup($group->value, 'core.admin') !== true) 
					{
						if ($groupaccess === null ) 
						{
							$html[] = JText::_('JRULE_NOT_ALLOWED');
						}
						else if ($groupaccess === false ) 
						{
							$html[] = JText::_('JRULE_FORBIDDEN');
						}
						else if ($groupaccess == true) 
						{
							$html[] = JText::_('JRULE_ALLOWED');
						}
					}
					else 
					{
						//Special handling for  groups that have global admin because they can't  be denied.
						//The admin rights can be changed.
						if ($action->name === 'core.admin') 
						{
							$html[] = JText::_('JRULE_ALLOWED');
						}
						elseif ($groupaccess === false || $globalrule === false) 
						{
							//Other actions cannot be changed.
							$html[] = JText::_('JRULE_ALLOWED_ADMIN_CONFLICT');
						}
						else 
						{
							$html[] = JText::_('JRULE_ALLOWED_ADMIN');
						}
					}
				}

				$html[] = '</td>';
				$html[] = '</tr>';
			}

			$html[] = '</tbody>';
			$html[] = '</table>';$html[] = JText::_('JRULE_CONFLICT_DESC');
			$html[] = '</div></div></div>';

		} // endforeach

		$html[] = str_repeat('</li></ul>', $curLevel);
		$html[] = '</ul>';
		$html[] = '</div>';

		$js = "window.addEvent('domready', function(){ new Accordion($$('div#permissions-sliders.pane-sliders .panel h3.jpane-toggler'), $$('div#permissions-sliders.pane-sliders .panel div.jpane-slider'), {onActive: function(toggler, i) {toggler.addClass('jpane-toggler-down');toggler.removeClass('jpane-toggler');Cookie.write('jpanesliders_permissions-sliders".$component."',$$('div#permissions-sliders.pane-sliders .panel h3').indexOf(toggler));},onBackground: function(toggler, i) {toggler.addClass('jpane-toggler');toggler.removeClass('jpane-toggler-down');},duration: 300,display: ".JRequest::getInt('jpanesliders_permissions-sliders'.$component, 0, 'cookie').",show: ".JRequest::getInt('jpanesliders_permissions-sliders'.$component, 0, 'cookie').",opacity: false}); });";

		JFactory::getDocument()->addScriptDeclaration($js);

		return implode("\n", $html);
	}

	/**
	 * Get a list of the user groups.
	 *
	 * @return	array
	 * @since	1.6
	 */
	protected function _getUserGroups()
	{
		// Initialise variables.
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true)
			->select('a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level')
			->from('#__usergroups AS a')
			->leftJoin('`#__usergroups` AS b ON a.lft > b.lft AND a.rgt < b.rgt')
			->group('a.id')
			->order('a.lft ASC');

		$db->setQuery($query);
		$options = $db->loadObjectList();

		return $options;
	}
}

