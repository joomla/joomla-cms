<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Legacy proxy for deprecated methods
 *
 * Please note that this class is used to support extensions moving from version 1.5 to 1.6.
 * These methods will not be available in version 1.7 onwards.
 *
 * @package		Joomla
 * @subpackage	System
 * @since		1.6
 */
final class JLegacy
{
	/**
	 * The maximum version supported by this legacy class
	 */
	const VERSION = 1.5;

	public static $template = null;

	/**
	 * Legacy support for deprecated JFactory::getTemplate.
	 *
	 * @return	JTemplate
	 */
	public static function JFactoryGetTemplate()
	{
		if (!is_object(self::$template)) {
			require_once dirname(__FILE__).DS.'template'.DS.'template.php';

			$conf	=& JFactory::getConfig();
			$tmpl	= new JTemplate;

			// patTemplate
			if ($conf->getValue('config.caching')) {
				 $tmpl->enableTemplateCache('File', JPATH_BASE.DS.'cache'.DS);
			}

			// load the wrapper and common templates
			$tmpl->setNamespace('jtmpl');
			$tmpl->readTemplatesFromFile('page.html');
			$tmpl->applyInputFilter('ShortModifiers');
			$tmpl->addGlobalVar('option', 		JRequest::getCmd('option'));
			$tmpl->addGlobalVar('self', 		$_SERVER['PHP_SELF']);
			$tmpl->addGlobalVar('uri_query', 	$_SERVER['QUERY_STRING']);
			$tmpl->addGlobalVar('REQUEST_URI',	JRequest::getURI());
			if (isset($GLOBALS['Itemid'])) {
				$tmpl->addGlobalVar('itemid', $GLOBALS['Itemid']);
			}

			self::$template = &$tmpl;
		}

		return self::$template;
	}
}