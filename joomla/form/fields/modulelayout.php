<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

require_once dirname(__FILE__).DS.'list.php';

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
	 * @var		string
	 */
	protected $_name = 'ModuleLayout';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		// Initialise variables.
		$options	= array();
		$path1		= null;
		$path2		= null;

		$module = $this->_element->attributes('module');
		if (empty($module)) {
			$module = $this->_form->getValue('module');
		}

		$clientId = $this->_element->attributes('client_id');
		if (empty($clientId)) {
			$clientId = $this->_form->getValue('client_id');
		}

		// Load template entries for each menuid
		$db			= JFactory::getDBO();
		$query		= 'SELECT template'
			. ' FROM #__templates_menu'
			. ' WHERE client_id = '.(int) $clientId.' AND menuid = 0';
		$db->setQuery($query);
		$template	= $db->loadResult();

		if ($module)
		{
			$module	= preg_replace('#\W#', '', $module);
			$path1	= JPATH_SITE.DS.'modules'.DS.$module.DS.'tmpl';
			$path2	= JPATH_SITE.DS.'templates'.DS.$template.DS.'html'.DS.$module;
			$options[]	= JHTML::_('select.option', '', '');
		}

		if ($path1 && $path2)
		{
			jimport('joomla.filesystem.file');
			$path1 = JPath::clean($path1);
			$path2 = JPath::clean($path2);

			$files	= JFolder::files($path1, '^[^_]*\.php$');
			foreach ($files as $file) {
				$options[]	= JHTML::_('select.option', JFile::stripExt($file));
			}

			if (is_dir($path2) && $files = JFolder::files($path2, '^[^_]*\.php$'))
			{
				$options[]	= JHTML::_('select.optgroup', JText::_('JOption_From_Default'));
				foreach ($files as $file) {
					$options[]	= JHTML::_('select.option', JFile::stripExt($file));
				}
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::_getOptions(), $options);

		return $options;
	}
}