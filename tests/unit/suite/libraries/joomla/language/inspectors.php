<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once JPATH_BASE.'/libraries/joomla/language/language.php';

/**
 * Inspector for protected properties and methods of the JLanguage class.
 */
class JLanguageInspector extends JLanguage
{
	public function getProperty($name)
	{
		return $this->$name;
	}

	public function parse($filename)
	{
		return parent::parse($filename);
	}
}