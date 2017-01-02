<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTML utility class for creating text diffs using jQuery, diff_patch_match.js and jquery.pretty-text-diff.js JavaScript libraries.
 *
 * @since  3.2
 */
abstract class JHtmlTextdiff
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  3.2
	 */
	protected static $loaded = array();

	/**
	 * Method to load Javascript text diff
	 *
	 * @param   string  $containerId  DOM id of the element where the diff will be rendered
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public static function textdiff($containerId)
	{
		// Only load once
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		// Depends on jQuery UI
		JHtml::_('bootstrap.framework');
		JHtml::_('script', 'com_contenthistory/diff_match_patch.js', array('version' => 'auto', 'relative' => true));
		JHtml::_('script', 'com_contenthistory/jquery.pretty-text-diff.min.js', array('version' => 'auto', 'relative' => true));
		JHtml::_('stylesheet', 'com_contenthistory/jquery.pretty-text-diff.css', array('version' => 'auto', 'relative' => true));

		// Attach diff to document
		JFactory::getDocument()->addScriptDeclaration("
			(function ($){
				$(document).ready(function (){
 					$('#" . $containerId . " tr').prettyTextDiff();
 				});
			})(jQuery);
			"
		);

		// Set static array
		static::$loaded[__METHOD__] = true;

		return;
	}
}
