<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.cache.cache');
jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Platform
 * @subpackage	Form
 * @since		11.1
 */
class JFormFieldCacheHandler extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	11.1
	 */
	public $type = 'CacheHandler';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	11.1
	 */
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Convert to name => name array.
		foreach (JCache::getStores() as $store) {
			$options[] = JHtml::_('select.option', $store, JText::_('JLIB_FORM_VALUE_CACHE_'.$store), 'value', 'text');
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}