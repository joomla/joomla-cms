<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Form Field class for the Joomla Platform.
 * Provides a list of available cache handlers
 *
 * @see    JCache
 * @since  1.7.0
 */
class CachehandlerField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'Cachehandler';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.7.0
	 */
	protected function getOptions()
	{
		$options = array();

		// Convert to name => name array.
		foreach (Cache::getStores() as $store)
		{
			$options[] = HTMLHelper::_('select.option', $store, Text::_('JLIB_FORM_VALUE_CACHE_' . $store), 'value', 'text');
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
