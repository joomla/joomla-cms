<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
require_once dirname(__FILE__).DS.'list.php';

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldCacheHandler extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'CacheHandler';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		jimport('joomla.cache.cache');
		$cacheOptions = JCache::getStores();
		foreach ($cacheOptions as $i=>$option) {
			$cacheOptions[$i]=new JObject(array('value'=>$option,'text'=>JText::_('JLIB_VALUE_CACHE_'.$option)));
		}
		$options	= array_merge(
						parent::_getOptions(),
						$cacheOptions
					);

		return $options;
	}
}
