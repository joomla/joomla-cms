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
 * Form Field to display a list of the layouts for a component view from the extension or default template overrides.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldComponentLayout extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'ComponentLayout';

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

		// Get the database object and a new query object.
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);

		// Build the query.
		$query->select('template');
		$query->from('#__template_styles');
		$query->where('client_id = 0');
		$query->where('home = 1');

		// Set the query and load the template.
		$db->setQuery($query, 0, 1);
		$template = $db->loadResult();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		// Get the extension.
		$extn = (string) $this->element['extension'];
		if (empty($extn) && ($this->form instanceof JForm)) {
			$extn = $this->form->getValue('extension');
		}
		$extn = preg_replace('#\W#', '', $extn);

		// Get the view.
		$view = (string) $this->element['view'];
		$view = preg_replace('#\W#', '', $view);

		// If a template, extension and view are present build the options.
		if ($template && $extn && $view) {

			// Build the search paths for layouts.
			$path1 = JPath::clean(JPATH_BASE.'/components/'.$extn.'/views/'.$view.'/tmpl');
			$path2 = JPath::clean(JPATH_BASE.'/templates/'.$template.'/html/'.$extn.'/'.$view);

			// Add the default option.
			$options[]	= JHTML::_('select.option', '', JText::_('JOption_Use_Menu_Request_Setting'));

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