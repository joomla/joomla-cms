<?php

/**
 * @version		$Id: category.php 13825 2009-12-23 01:03:06Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_BASE') or die;

// Import html library
jimport('joomla.html.html');

// Import joomla field list class
require_once dirname(__FILE__) . DS . 'list.php';

/**
 * Supports an HTML select list of file
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldFileList extends JFormFieldList
{

	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'FileList';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions() 
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		// path to files directory
		$path = JPATH_ROOT . '/' . $this->_element->attributes('directory');
		$filter = $this->_element->attributes('filter');
		$exclude = $this->_element->attributes('exclude');
		$stripExt = $this->_element->attributes('stripext');
		$files = JFolder::files($path, $filter);

		// Prepare return value
		$options = array();

		// Add basic options
		if (!$this->_element->attributes('hide_none')) 
		{
			$options[] = JHtml::_('select.option', '-1', JText::_('JOption_Do_Not_Use'));
		}
		if (!$this->_element->attributes('hide_default')) 
		{
			$options[] = JHtml::_('select.option', '', JText::_('JOption_Use_Default'));
		}

		// Iterate over files
		if (is_array($files)) 
		{
			foreach($files as $file) 
			{
				if ($exclude) 
				{
					if (preg_match(chr(1) . $exclude . chr(1), $file)) 
					{
						continue;
					}
				}
				if ($stripExt) 
				{
					$file = JFile::stripExt($file);
				}
				$options[] = JHtml::_('select.option', $file, $file);
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::_getOptions(), $options);
		return $options;
	}
}

