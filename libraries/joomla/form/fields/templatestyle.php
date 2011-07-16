<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('groupedlist');

/**
 * Form Field class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldTemplateStyle extends JFormFieldGroupedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'TemplateStyle';

	/**
	 * Method to get the field option groups.
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 * @since   11.1
	 */
	protected function getGroups()
	{
		// Initialize variables.
		$groups = array();
		$lang = JFactory::getLanguage();

		// Get the client and client_id.
		$clientName = $this->element['client'] ? (string) $this->element['client'] : 'site';
		$client = JApplicationHelper::getClientInfo($clientName, true);

		// Get the template.
		$template = (string) $this->element['template'];

		// Get the database object and a new query object.
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);

		// Build the query.
		$query->select('s.id, s.title, e.name as name, s.template');
		$query->from('#__template_styles as s');
		$query->where('s.client_id = '.(int) $client->id);
		$query->order('template');
		$query->order('title');
		if ($template) {
			$query->where('s.template = '.$db->quote($template));
		}
		$query->join('LEFT', '#__extensions as e on e.element=s.template');
		$query->where('e.enabled=1');

		// Set the query and load the styles.
		$db->setQuery($query);
		$styles = $db->loadObjectList();

		// Build the grouped list array.
		if ($styles)
		{
			foreach($styles as $style) {
				$template = $style->template;
				$lang->load('tpl_'.$template.'.sys', $client->path, null, false, false)
			||	$lang->load('tpl_'.$template.'.sys', $client->path.'/templates/'.$template, null, false, false)
			||	$lang->load('tpl_'.$template.'.sys', $client->path, $lang->getDefault(), false, false)
			||	$lang->load('tpl_'.$template.'.sys', $client->path.'/templates/'.$template, $lang->getDefault(), false,false);
				$name = JText::_($style->name);
				// Initialize the group if necessary.
				if (!isset($groups[$name])) {
					$groups[$name] = array();
				}

				$groups[$name][] = JHtml::_('select.option', $style->id, $style->title);
			}
		}

		// Merge any additional groups in the XML definition.
		$groups = array_merge(parent::getGroups(), $groups);

		return $groups;
	}
}
