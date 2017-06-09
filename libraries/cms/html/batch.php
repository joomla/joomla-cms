<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Extended Utility class for batch processing widgets.
 *
 * @since       1.7
 *
 * @deprecated  4.0 Use JLayout directly
 */
abstract class JHtmlBatch
{
	/**
	 * Display a batch widget for the access level selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @since       1.7
	 *
	 * @deprecated  4.0 instead of JHtml::_('batch.access'); use JLayoutHelper::render('joomla.html.batch.access', array());
	 */
	public static function access()
	{
		JLog::add('The use of JHtml::_("batch.access") is deprecated use JLayout instead.', JLog::WARNING, 'deprecated');

		return JLayoutHelper::render('joomla.html.batch.access', array());
	}

	/**
	 * Displays a batch widget for moving or copying items.
	 *
	 * @param   string  $extension  The extension that owns the category.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @since       1.7
	 *
	 * @deprecated  4.0 instead of JHtml::_('batch.item'); use JLayoutHelper::render('joomla.html.batch.item', array('extension' => 'com_XXX'));
	 */
	public static function item($extension)
	{
		$displayData = array('extension' => $extension);

		JLog::add('The use of JHtml::_("batch.item") is deprecated use JLayout instead.', JLog::WARNING, 'deprecated');

		return JLayoutHelper::render('joomla.html.batch.item', $displayData);
	}

	/**
	 * Display a batch widget for the language selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @since       2.5
	 *
	 * @deprecated  4.0 instead of JHtml::_('batch.language'); use JLayoutHelper::render('joomla.html.batch.language', array());
	 */
	public static function language()
	{
		JLog::add('The use of JHtml::_("batch.language") is deprecated use JLayout instead.', JLog::WARNING, 'deprecated');

		return JLayoutHelper::render('joomla.html.batch.language', array());
	}

	/**
	 * Display a batch widget for the user selector.
	 *
	 * @param   boolean  $noUser  Choose to display a "no user" option
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @since       2.5
	 *
	 * @deprecated  4.0 instead of JHtml::_('batch.user'); use JLayoutHelper::render('joomla.html.batch.user', array());
	 */
	public static function user($noUser = true)
	{
		$displayData = array('noUser' => $noUser);

		JLog::add('The use of JHtml::_("batch.user") is deprecated use JLayout instead.', JLog::WARNING, 'deprecated');

		return JLayoutHelper::render('joomla.html.batch.user', $displayData);
	}

	/**
	 * Display a batch widget for the tag selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @since       3.1
	 *
	 * @deprecated  4.0 instead of JHtml::_('batch.tag'); use JLayoutHelper::render('joomla.html.batch.tag', array());
	 */
	public static function tag()
	{
		JLog::add('The use of JHtml::_("batch.tag") is deprecated use JLayout instead.', JLog::WARNING, 'deprecated');

		return JLayoutHelper::render('joomla.html.batch.tag', array());
	}
}
