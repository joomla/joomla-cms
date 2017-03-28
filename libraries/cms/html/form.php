<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Utility class for form elements
 *
 * @since  1.5
 */
abstract class JHtmlForm
{
	/**
	 * Displays a hidden token field to reduce the risk of CSRF exploits
	 *
	 * Use in conjunction with JSession::checkToken()
	 *
	 * @params  array   $attribs  Input element attributes.
	 *
	 * @return  string  A hidden input field with a token
	 *
	 * @see     JSession::checkToken()
	 * @since   1.5
	 */
	public static function token($attribs = null)
	{
		if (is_array($attribs))
		{
			$attribs = ' ' . ArrayHelper::toString($attribs);
		}

		return '<input type="hidden" name="' . JSession::getFormToken() . '" value="1"' . $attribs . ' />';
	}

	/**
	 * Add CSRF form token to <head> meta that developers can get it by Javascript.
	 *
	 * @param   string  $name  The name of this meta tag.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function csrf($name = 'csrf-token')
	{
		/** @var JDocumentHtml $doc */
		$doc = JFactory::getDocument();

		if ($doc->getType() !== 'html' || !$doc instanceof JDocumentHtml)
		{
			return;
		}

		$doc->setMetaData($name, JSession::getFormToken());
	}
}
