<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language;

defined('_JEXEC') or die;

/**
 * Default factory for creating language objects
 *
 * @since  __DEPLOY_VERSION__
 */
class LanguageFactory implements LanguageFactoryInterface
{
	/**
	 * Method to get an instance of a language.
	 *
	 * @param   string   $lang   The language to use
	 * @param   boolean  $debug  The debug mode
	 *
	 * @return  Language
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createLanguage($lang, $debug = false): Language
	{
		return new Language($lang, $debug);
	}
}
