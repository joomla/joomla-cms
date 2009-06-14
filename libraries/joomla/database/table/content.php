<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.database.table');

/**
 * Content table
 *
 * @package 	Joomla.Framework
 * @subpackage		Table
 * @since	1.0
 */
class JTableContent extends JTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $title				= null;
	/** @var string */
	var $alias				= null;
	/** @var string */
	var $title_alias			= null;
	/** @var string */
	var $introtext			= null;
	/** @var string */
	var $fulltext			= null;
	/** @var int */
	var $state				= null;
	/** @var int DEPRECATED */
	var $sectionid			= null;
	/** @var int DEPRECATED */
	var $mask				= null;
	/** @var int */
	var $catid				= null;
	/** @var datetime */
	var $created				= null;
	/** @var int User id*/
	var $created_by			= null;
	/** @var string An alias for the author*/
	var $created_by_alias		= null;
	/** @var datetime */
	var $modified			= null;
	/** @var int User id*/
	var $modified_by			= null;
	/** @var boolean */
	var $checked_out			= 0;
	/** @var time */
	var $checked_out_time		= 0;
	/** @var datetime */
	var $publish_up			= null;
	/** @var datetime */
	var $publish_down		= null;
	/** @var string */
	var $images				= null;
	/** @var string */
	var $urls				= null;
	/** @var string */
	var $attribs				= null;
	/** @var int */
	var $version				= null;
	/** @var int */
	var $parentid			= null;
	/** @var int */
	var $ordering			= null;
	/** @var string */
	var $metakey				= null;
	/** @var string */
	var $metadesc			= null;
	/** @var string */
	var $metadata			= null;
	/** @var int */
	var $access				= null;
	/** @var int */
	var $hits				= null;

	/**
	* @param database A database connector object
	*/
	function __construct(&$db)
	{
		parent::__construct('#__content', 'id', $db);

		$this->access	= (int)JFactory::getConfig()->getValue('access');
	}

	/**
	 * Method to return the access section name for the asset table.
	 *
	 * @access	public
	 * @return	string
	 * @since	1.6
	 */
	function getAssetSection()
	{
		return 'com_content';
	}

	/**
	 * Method to return the name prefix to use for the asset table.
	 *
	 * @access	public
	 * @return	string
	 * @since	1.6
	 */
	function getAssetNamePrefix()
	{
		return 'article';
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @access	public
	 * @return	string
	 * @since	1.0
	 */
	function getAssetTitle()
	{
		return $this->title;
	}

	/**
	 * Overloaded check function
	 *
	 * @access public
	 * @return boolean
	 * @see JTable::check
	 * @since 1.5
	 */
	function check()
	{
		/*
		TODO: This filter is too rigorous,need to implement more configurable solution
		// specific filters
		$filter = & JFilterInput::getInstance(null, null, 1, 1);
		$this->introtext = trim($filter->clean($this->introtext));
		$this->fulltext =  trim($filter->clean($this->fulltext));
		*/

		if (empty($this->title)) {
			$this->setError(JText::_('Article must have a title'));
			return false;
		}

		if (empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = JFilterOutput::stringURLSafe($this->alias);

		if (trim(str_replace('-','',$this->alias)) == '') {
			$datenow = &JFactory::getDate();
			$this->alias = $datenow->toFormat("%Y-%m-%d-%H-%M-%S");
		}

		if (trim(str_replace('&nbsp;', '', $this->fulltext)) == '') {
			$this->fulltext = '';
		}

		if (empty($this->introtext) && empty($this->fulltext)) {
			$this->setError(JText::_('Article must have some text'));
			return false;
		}

		// clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if (!empty($this->metakey)) { // only process if not empty
			$bad_characters = array("\n", "\r", "\"", "<", ">"); // array of characters to remove
			$after_clean = JString::str_ireplace($bad_characters, "", $this->metakey); // remove bad characters
			$keys = explode(',', $after_clean); // create array using commas as delimiter
			$clean_keys = array();
			foreach($keys as $key) {
				if (trim($key)) {  // ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}
			$this->metakey = implode(", ", $clean_keys); // put array back together delimited by ", "
		}

		// clean up description -- eliminate quotes and <> brackets
		if (!empty($this->metadesc)) { // only process if not empty
			$bad_characters = array("\"", "<", ">");
			$this->metadesc = JString::str_ireplace($bad_characters, "", $this->metadesc);
		}

		return true;
	}

	/**
	* Converts record to XML
	* @param boolean Map foreign keys to text values
	*/
	function toXML($mapKeysToText=false)
	{
		$db = &JFactory::getDbo();

		if ($mapKeysToText) {
			$query = 'SELECT name'
			. ' FROM #__categories'
			. ' WHERE id = '. (int) $this->catid
			;
			$db->setQuery($query);
			$this->catid = $db->loadResult();

			$query = 'SELECT name'
			. ' FROM #__users'
			. ' WHERE id = ' . (int) $this->created_by
			;
			$db->setQuery($query);
			$this->created_by = $db->loadResult();
		}

		return parent::toXML($mapKeysToText);
	}
}
