<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.database.table');

/**
 * Languages table.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.6
 */
class JTableLanguage extends JTable
{
	/**
	 * The primary key.
	 *
	 * @var	unsigned int
	 */
	public $lang_id;

	/**
	 * The language code: xx-XX through to yyy-YYY.
	 *
	 * @var	char
	 */
	public $lang_code;

	/**
	 * The english name for the language.
	 *
	 * @var	varchar
	 */
	public $title;

	/**
	 * The native name for the language.
	 *
	 * @var	varchar
	 */
	public $title_native;

	/**
	 * A description for the language.
	 *
	 * @var	varchar
	 */
	public $description;

	/**
	 * A Meta Description for the language.
	 *
	 * @var	varchar
	 */
	public $metadesc;

	/**
	 * The Meta keywords for the language.
	 *
	 * @var	varchar
	 */
	public $metakey;

	/**
	 * The published state of the language.
	 *
	 * @var	int
	 */
	public $published;

	/**
	 * Constructor
	 *
	 * @param	JDatabase
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__languages', 'lang_id', $db);
	}

	/**
	 * Overloaded check method to ensure data integrity
	 *
	 * @return boolean True on success
	 */
	public function check()
	{
		if (trim($this->title) == '') {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_LANGUAGE_NO_TITLE'));
			return false;
		}

		return true;
	}
}
