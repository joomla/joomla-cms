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
jimport('joomla.form.formfield');
JLoader::register('JFormFieldGroupedList', dirname(__FILE__).'/groupedlist.php');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldTemplateStyle extends JFormFieldGroupedList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'TemplateStyle';

	/**
	 * Method to get the field option groups.
	 *
	 * @return	array	The field option objects as a nested array in groups.
	 * @since	1.6
	 */
	protected function getGroups()
	{
		// Initialize variables.
		$groups = array();

		// Get the client and client_id.
		$client = (string) $this->element['client'];
		$clientId = ($client == 'administrator') ? 1 : 0;

		// Get the database object and a new query object.
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);

		// Build the query.
		$query->select('id, title, template');
		$query->from('#__template_styles');
		$query->where('client_id = '.(int) $clientId);
		$query->order('template');
		$query->order('title');

		// Set the query and load the styles.
		$db->setQuery($query);
		$styles = $db->loadObjectList();

		// Build the grouped list array.
		foreach($styles as $style) {

			// Initialize the group if necessary.
			if (!isset($groups[$style->template])) {
				$groups[$style->template] = array();
			}

			$groups[$style->template][] = JHtml::_('select.option', $style->id, $style->title);
		}

		// Merge any additional groups in the XML definition.
		$groups = array_merge(parent::getGroups(), $groups);

		return $groups;
	}
}