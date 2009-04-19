<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('JPATH_BASE') or die('Restricted Access');

jimport('joomla.html.html');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @since		1.6
 */
class JFormFieldCachehandlers extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Cachehandlers';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		jimport('joomla.cache.cache');
		$options = array();
		foreach(JCache::getStores() as $store) {
			$options[] = JHtml::_('select.option', $store, JText::_(ucfirst($store)) );
		}

		$options	= array_merge(
						parent::_getOptions(),
						$options
					);

		return $options;
	}
}