<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
JFormHelper::loadFieldClass('radio');

/**
 * Sample data Form Field class.
 *
 * @package  Joomla.Installation
 * @since    1.6
 */
class JFormFieldSample extends JFormFieldRadio
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   1.6
	 */
	protected $type = 'Sample';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$lang = JFactory::getLanguage();
		$options = array();
		$type = $this->form->getValue('db_type');

		// Some database drivers share DDLs; point these drivers to the correct parent
		if ($type == 'mysqli')
		{
			$type = 'mysql';
		}
		elseif ($type == 'sqlsrv')
		{
			$type = 'sqlazure';
		}

		// Get a list of files in the search path with the given filter.
		$files = JFolder::files(JPATH_INSTALLATION . '/sql/' . $type, '^sample.*\.sql$');

		// Add option to not install sampledata.
		$options[] = JHtml::_('select.option', '', 'INSTL_SITE_INSTALL_SAMPLE_NONE');

		// Build the options list from the list of files.
		if (is_array($files))
		{
			foreach ($files as $file)
			{
				$options[] = JHtml::_('select.option', $file, $lang->hasKey($key = 'INSTL_' . ($file = JFile::stripExt($file)) . '_SET') ?
					JHtml::_('tooltip', JText::_('INSTL_' . strtoupper($file = JFile::stripExt($file)) . '_SET_DESC'), '', '',
						JText::_('INSTL_' . ($file = JFile::stripExt($file)) . '_SET')
					) : $file
				);
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		if (!$this->value)
		{
			$conf = JFactory::getConfig();
			if ($conf->get('sampledata'))
			{
				$this->value = $conf->get('sampledata');
			}
			else
			{
				$this->value = '';
			}
		}

		return parent::getInput();
	}
}
