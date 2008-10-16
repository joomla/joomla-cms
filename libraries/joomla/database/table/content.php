<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Table
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();


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
	protected $id					= null;
	/** @var string */
	protected $title				= null;
	/** @var string */
	protected $alias				= null;
	/** @var string */
	protected $title_alias			= null;
	/** @var string */
	protected $introtext			= null;
	/** @var string */
	protected $fulltext			= null;
	/** @var int */
	protected $state				= null;
	/** @var int The id of the category section*/
	protected $sectionid			= null;
	/** @var int DEPRECATED */
	protected $mask				= null;
	/** @var int */
	protected $catid				= null;
	/** @var datetime */
	protected $created				= null;
	/** @var int User id*/
	protected $created_by			= null;
	/** @var string An alias for the author*/
	protected $created_by_alias		= null;
	/** @var datetime */
	protected $modified			= null;
	/** @var int User id*/
	protected $modified_by			= null;
	/** @var boolean */
	protected $checked_out			= 0;
	/** @var time */
	protected $checked_out_time		= 0;
	/** @var datetime */
	protected $frontpage_up		= null;
	/** @var datetime */
	protected $frontpage_down		= null;
	/** @var datetime */
	protected $publish_up			= null;
	/** @var datetime */
	protected $publish_down		= null;
	/** @var string */
	protected $images				= null;
	/** @var string */
	protected $urls				= null;
	/** @var string */
	protected $attribs				= null;
	/** @var int */
	protected $version				= null;
	/** @var int */
	protected $parentid			= null;
	/** @var int */
	protected $ordering			= null;
	/** @var string */
	protected $metakey				= null;
	/** @var string */
	protected $metadesc			= null;
	/** @var string */
	protected $metadata			= null;
	/** @var int */
	protected $access				= null;
	/** @var int */
	protected $hits				= null;

	/**
	* @param database A database connector object
	*/
	protected function __construct( &$db ) {
		parent::__construct( '#__content', 'id', $db );
	}

	/**
	 * Overloaded check function
	 *
	 * @access public
	 * @return boolean
	 * @see JTable::check
	 * @since 1.5
	 */
	public function check()
	{
		/*
		TODO: This filter is too rigorous,need to implement more configurable solution
		// specific filters
		$filter = & JFilterInput::getInstance( null, null, 1, 1 );
		$this->introtext = trim( $filter->clean( $this->introtext ) );
		$this->fulltext =  trim( $filter->clean( $this->fulltext ) );
		*/


		if(empty($this->title)) {
			$this->setError(JText::_('Article must have a title'));
			return false;
		}

		if(empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = JFilterOutput::stringURLSafe($this->alias);

		if(trim(str_replace('-','',$this->alias)) == '') {
			$datenow =& JFactory::getDate();
			$this->alias = $datenow->toFormat("%Y-%m-%d-%H-%M-%S");
		}

		if (trim( str_replace( '&nbsp;', '', $this->fulltext ) ) == '') {
			$this->fulltext = '';
		}

		if(empty($this->introtext) && empty($this->fulltext)) {
			$this->setError(JText::_('Article must have some text'));
			return false;
		}

		return true;
	}

	/**
	* Converts record to XML
	* @param boolean Map foreign keys to text values
	*/
	public function toXML( $mapKeysToText=false )
	{
		$db =& JFactory::getDBO();
		try {
			if ($mapKeysToText) {
				$query = 'SELECT name'
				. ' FROM #__sections'
				. ' WHERE id = '. (int) $this->sectionid
				;
				$db->setQuery( $query );
				$this->sectionid = $db->loadResult();
	
				$query = 'SELECT name'
				. ' FROM #__categories'
				. ' WHERE id = '. (int) $this->catid
				;
				$db->setQuery( $query );
				$this->catid = $db->loadResult();
	
				$query = 'SELECT name'
				. ' FROM #__users'
				. ' WHERE id = ' . (int) $this->created_by
				;
				$db->setQuery( $query );
				$this->created_by = $db->loadResult();
			}
		} catch(JException $e) {
			$this->setError($e->getMessage());
			return false;
		}

		return parent::toXML( $mapKeysToText );
	}
}
