<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Messages
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Messages Component Messages Model
 *
 * @package		Joomla
 * @subpackage	Messages
 * @since 1.5
 */
class MessagesModelConfig extends JModel
{
	/**
	 * Data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * User ID
	 *
	 * @var string
	 */
	var $_user_id = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Method to get contacts item data
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$this->_loadData();
		}

		return $this->_data;
	}

	function _loadData()
	{
		$user				=& JFactory::getUser();

		$query = 'SELECT cfg_name, cfg_value'
			. ' FROM #__messages_cfg'
			. ' WHERE user_id = '.(int) $user->get('id')
		;
		$this->_db->setQuery( $query );
		$data = $this->_db->loadObjectList( 'cfg_name' );

		// initialize values if they do not exist
		$this->_data['lock'] = isset($data['lock']->cfg_value) ? $data['lock']->cfg_value : 0;
		$this->_data['mail_on_new'] = isset($data['mail_on_new']->cfg_value) ? $data['mail_on_new']->cfg_value : 0;
		$this->_data['auto_purge'] = isset($data['auto_purge']->cfg_value) ? $data['auto_purge']->cfg_value : 7;
	}

	/**
	 * Method to store the config info
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($vars)
	{
		$user	=& JFactory::getUser();

		$query = 'DELETE FROM #__messages_cfg'
			. ' WHERE user_id = '.(int) $user->get('id')
		;
		$this->_db->setQuery( $query );
		if ($this->_db->query() === false)
			return false;

		// Multi-row INSERT query
		$values = array();
		foreach ($vars as $k=>$v) {
			$values[] = '( '.(int) $user->get('id').', '.$this->_db->Quote($k).', '.$this->_db->Quote($this->_db->getEscaped( $v )).' )';
		}
		if (count($values)) {
			$query = 'INSERT INTO #__messages_cfg'
				. ' ( user_id, cfg_name, cfg_value )'
				. ' VALUES '
				. implode( ', ', $values )
			;
			$this->_db->setQuery( $query );
			if ($this->_db->query() === false)
				return false;
		}

		return true;
	}
}