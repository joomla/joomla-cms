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
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.form.formfield');
JLoader::register('JFormFieldList', dirname(__FILE__).'/list.php');

/**
 * Form Field to display a list of the layouts for a module view from the module or default template overrides.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldModuleLayout extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'ModuleLayout';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options	= array();
		$path1		= null;
		$path2		= null;

		// Get the client id.
		$clientId = (int) $this->element['client_id'];
		if (empty($clientId) && (!$this->form instanceof JForm)) {
			$clientId = (int) $this->form->getValue('client_id');
		}
		$client	= JApplicationHelper::getClientInfo($clientId);

		// Get the database object and a new query object.
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);

		// Build the query.
		$query->select('template');
		$query->from('#__template_styles');
		$query->where('client_id = '.(int) $clientId);
		$query->where('home = 1');

		// Set the query and load the template.
		$db->setQuery($query, 0, 1);
		$template = $db->loadResult();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		// Get the module.
		$module = (string) $this->element['module'];
		if (empty($module) && ($this->form instanceof JForm)) {
			$module = $this->form->getValue('module');
		}
		$module = preg_replace('#\W#', '', $module);

		// If a template, extension and view are present build the options.
		if ($template && $module && $client) {

			// Build the search paths for layouts.
			$path1 = JPath::clean($client->path.'/modules/'.$module.'/tmpl');
			$path2 = JPath::clean($client->path.'/templates/'.$template.'/html/'.$module);

			// Add the default option.
			$options[]	= JHTML::_('select.option', '', JText::_('JOption_Use_Default_Module_Setting'));

			// Add the layout options from the first path.
			if (is_dir($path1) && ($files = JFolder::files($path1, '^[^_]*\.php$'))) {
				foreach ($files as $file) {
					$options[]	= JHTML::_('select.option', JFile::stripExt($file));
				}
			}

			// Add the layout options from the second path.
			if (is_dir($path2) && ($files = JFolder::files($path2, '^[^_]*\.php$'))) {
				$options[]	= JHTML::_('select.optgroup', JText::_('JOption_From_Default_Template'));
				foreach ($files as $file) {
					$options[]	= JHTML::_('select.option', JFile::stripExt($file));
				}
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}