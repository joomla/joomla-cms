<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTML utility class for creating text diffs using jQuery, diff_patch_match.js and jquery.pretty-text-diff.js JavaScript libraries.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory.HTML
 * @since       3.2
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
		if (isset(self::$loaded[__METHOD__]))
		{
			return;
		}

		// Depends on jQuery UI
		$document = JFactory::getDocument();
		JHtml::_('bootstrap.framework');
		$document->addScript(JUri::root(true) . '/administrator/components/com_contenthistory/media/js/diff_match_patch.js', 'text/javascript', true);
		$document->addScript(JUri::root(true) . '/administrator/components/com_contenthistory/media/js/jquery.pretty-text-diff.min.js', 'text/javascript', true);
		$document->addStyleSheet(JUri::root(true) . '/administrator/components/com_contenthistory/media/css/jquery.pretty-text-diff.css');

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
		self::$loaded[__METHOD__] = true;

		return;
	}
}
