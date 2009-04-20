<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Weblink Table class
 *
 * @package		Joomla.Administrator
 * @subpackage	Weblinks
 * @since		1.5
 */
class WeblinksTableWeblink extends JTableAsset
{
	/**
	 * Primary Key
	 *
	 * @var int
	 */
	protected $id = null;

	/**
	 * @var int
	 */
	protected $catid = null;

	/**
	 * @var int
	 */
	protected $sid = null;

	/**
	 * @var string
	 */
	protected $title = null;

	/**
	 * @var string
	 */
	protected $alias = null;

	/**
	 * @var string
	 */
	protected $url = null;

	/**
	 * @var string
	 */
	protected $description = null;

	/**
	 * @var datetime
	 */
	protected $date = null;

	/**
	 * @var int
	 */
	protected $hits = null;

	/**
	 * @var int
	 */
	protected $state = null;

	/**
	 * @var boolean
	 */
	protected $checked_out = 0;

	/**
	 * @var time
	 */
	protected $checked_out_time = 0;

	/**
	 * @var int
	 */
	protected $ordering = null;

	/**
	 * @var int
	 */
	protected $archived = null;

	/**
	 * @var int
	 */
	protected $approved = null;

	/**
	 * @var string
	 */
	protected $params = null;

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	protected function __construct(& $db)
	{
		parent::__construct('#__weblinks', 'id', $db);
	}

	protected function getAssetSection()
	{
		return 'com_weblinks';
	}
	
	protected function getAssetNamePrefix()
	{
		return 'weblink';
	}
	
	protected function getAssetTitle()
	{
		return $this->title;
	}
	
	/**
	 * Loads a weblinks, and any other necessary data
	 *
	 * @param	integer		$id		An optional user id.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.6
	 */
	public function load($id = null)
	{
		if ($result = parent::load($id)) {
			$this->params = json_decode($this->params);
		}

		return $result;
	}

	/**
	 * Stores a weblink
	 *
	 * @param	boolean		$updateNulls	Toggle whether null values should be updated.
	 * @return	boolean		True on success, false on failure.
	 * @since	1.6
	 */
	public function store($updateNulls = false)
	{
		// Transform the params field
		if (is_array($this->params)) {
			$registry = new JRegistry();
			$registry->loadArray($this->params);
			$this->params = $registry->toString();
		}

		// Attempt to store the user data.
		return parent::store($updateNulls);
	}

	/**
	 * Overloaded check method to ensure data integrity
	 *
	 * @access public
	 * @return boolean True on success
	 * @since 1.0
	 */
	public function check()
	{
		if (JFilterInput::checkAttribute(array ('href', $this->url))) {
			$this->setError(JText::_('Please provide a valid URL'));
			return false;
		}

		/** check for valid name */
		if (trim($this->title) == '') {
			$this->setError(JText::_('Your Weblink must contain a title.'));
			return false;
		}

		if (!(eregi('http://', $this->url) || (eregi('https://', $this->url)) || (eregi('ftp://', $this->url)))) {
			$this->url = 'http://'.$this->url;
		}

		/** check for existing name */
		$query = 'SELECT id FROM #__weblinks WHERE title = '.$this->_db->Quote($this->title).' AND catid = '.(int) $this->catid;
		$this->_db->setQuery($query);

		$xid = intval($this->_db->loadResult());
		if ($xid && $xid != intval($this->id)) {
			$this->setError(JText::sprintf('WARNNAMETRYAGAIN', JText::_('Web Link')));
			return false;
		}

		if (empty($this->alias)) {
			$this->alias = $this->title;
		}
		$this->alias = JFilterOutput::stringURLSafe($this->alias);
		if (trim(str_replace('-','',$this->alias)) == '') {
			$datenow =& JFactory::getDate();
			$this->alias = $datenow->toFormat("%Y-%m-%d-%H-%M-%S");
		}

		return true;
	}
}
