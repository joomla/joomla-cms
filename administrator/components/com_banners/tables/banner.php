<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Banner table
 *
 * @package		Joomla.Framework
 * @subpackage	com_banners
 * @since		1.6
 */
class BannersTableBanner extends JTable
{
	/** @var int */
	var $id				= null;
	/** @var int */
	var $cid				= null;
	/** @var int */
	var $type				= 0;
	/** @var string */
	var $name				= '';
	/** @var string */
	var $alias				= '';
	/** @var int */
	var $imptotal			= 0;
	/** @var int */
	var $impmade			= 0;
	/** @var int */
	var $clicks				= 0;
	/** @var string */
	var $clickurl			= '';
	/** @var int */
	var $state				= 0;
	/** @var int */
	var $catid				= null;
	/** @var string */
	var $description		= null;
	/** @var int */
	var $sticky				= null;
	/** @var int */
	var $ordering			= null;
	/** @var string */
	var $metakey		= null;
	/** @var string */
	var $params				= null;
	/** @var int */
	var $own_prefix			= 0;
	/** @var string */
	var $metakey_prefix	= null;
	/** @var int */
	var $purchase_type		= 0;
	/** @var int */
	var $track_clicks		= 0;
	/** @var int */
	var $track_impressions	= 0;
	/** @var int */
	var $checked_out		= 0;
	/** @var date */
	var $checked_out_time	= 0;
	/** @var date */
	var $publish_up			= null;
	/** @var date */
	var $publish_down		= null;
	/** @var date creation date */
	var $created			= null;
	/** @var date reset date */
	var $reset			= null;

	function __construct(&$_db)
	{
		parent::__construct('#__banners', 'id', $_db);
		$this->created = JFactory::getDate()->toMySQL();
	}

	function clicks()
	{
		$query = 'UPDATE #__banners'
		. ' SET clicks = (clicks + 1)'
		. ' WHERE id = ' . (int) $this->id
		;
		$this->_db->setQuery($query);
		$this->_db->query();
	}

	/**
	 * Overloaded check function
	 *
	 * @return boolean
	 * @see JTable::check
	 * @since 1.5
	 */
	function check()
	{
		jimport('joomla.filter.output');

		// Set name
		$this->name = htmlspecialchars_decode($this->name, ENT_QUOTES);

		// Set alias
		$this->alias = JApplication::stringURLSafe($this->alias);
		if (empty($this->alias)) {
			$this->alias = JApplication::stringURLSafe($this->name);
		}

		// Set ordering
		if($this->state<0) {
			// Set ordering to 0 if state is archived or trashed
			$this->ordering = 0;
		}
		elseif(empty($this->ordering)) {
			// Set ordering to last if ordering was 0
			$this->ordering = self::getNextOrder('`catid`=' . $this->_db->Quote($this->catid).' AND state>=0');
		}

		return true;
	}
	/**
	 * Overloaded bind function
	 *
	 * @param	array		$hash named array
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @see JTable:bind
	 * @since 1.5
	 */
	public function bind($array, $ignore = array())
	{
		if (!isset($array['params']))
		{
			$parameter = new JParameter;
			$params=array();
			// custom group
			if (isset($array['custom']) && is_array($array['custom']))
			{
				$params['custom']=$array['custom'];
			}
			if (isset($array['alt']) && is_array($array['alt']))
			{
				$params['alt']=$array['alt'];
			}
			if (isset($array['flash']) && is_array($array['flash']))
			{
				$params['flash']=$array['flash'];
			}
			if (isset($array['image']) && is_array($array['image']))
			{
				$params['image']=$array['image'];
			}
			// encode params to JSON
			$parameter->loadArray($params);
			$array['params'] = $parameter->toString();
		}
		return parent::bind($array, $ignore);
	}
	/**
	 * method to store a row
	 *
	 * @param boolean $updateNulls True to update fields even if they are null.
	 */
	function store($updateNulls = false)
	{
		if (empty($this->id))
		{
			$purchase_type = $this->purchase_type;
			if ($purchase_type < 0 && $this->cid)
			{
				$client = JTable::getInstance('Client','BannersTable');
				$client->load($this->cid);
				$purchase_type = $client->purchase_type;
			}
			if ($purchase_type < 0)
			{
				$params = JComponentHelper::getParams('com_banners');
				$purchase_type = $params->get('purchase_type');
			}

			switch($purchase_type)
			{
			case 1:
				$this->reset='0000-00-00 00:00:00';
			break;
			case 2:
				$this->reset = JFactory::getDate('+1 year '.date('Y-m-d',strtotime('now')))->toMySQL();
			break;
			case 3:
				$this->reset = JFactory::getDate('+1 month '.date('Y-m-d',strtotime('now')))->toMySQL();
			break;
			case 4:
				$this->reset = JFactory::getDate('+7 day '.date('Y-m-d',strtotime('now')))->toMySQL();
			break;
			case 5:
				$this->reset = JFactory::getDate('+1 day '.date('Y-m-d',strtotime('now')))->toMySQL();
			break;
			}
			// Store the row
			parent::store($updateNulls);
		}
		else
		{
			// Get the old row
			$oldrow = & JTable::getInstance('Banner', 'BannersTable');
			if (!$oldrow->load($this->id) && $oldrow->getError())
			{
				$this->setError($oldrow->getError());
			}

			// Store the new row
			parent::store($updateNulls);

			// Need to reorder ?
			if ($oldrow->state>=0 && ($this->state < 0 || $oldrow->catid != $this->catid))
			{
				// Reorder the oldrow
				$this->reorder('`catid`=' . $this->_db->Quote($oldrow->catid).' AND state>=0');
			}
		}
		return count($this->getErrors())==0;
	}
	/**
	 * Overloaded load function
	 *
	 * @param	int $pk primary key
	 * @param	boolean $reset reset data
	 * @return	boolean
	 * @see JTable:load
	 */
	public function load($pk = null, $reset = true)
	{
		if (parent::load($pk, $reset))
		{
			// Convert the params field to a parameter.
			$registry = new JRegistry;
			$registry->loadJSON($this->params);
			$this->params = $registry;
			// Set customcode
			$this->params->setValue('custom.bannercode', JFilterOutput::objectHTMLSafe( $this->params->getValue('custom.bannercode',''), ENT_QUOTES));
			return true;
		}
		else
		{
			return false;
		}
	}
	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param	mixed	An optional array of primary key values to update.  If not
	 * 					set the instance property value is used.
	 * @param	integer The publishing state. eg. [0 = unpublished, 1 = published, -1=archived, -2=trashed]
	 * @param	integer The user id of the user performing the operation.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k) {
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else {
				$this->setError(JText::_('No_Rows_Selected'));
				return false;
			}
		}

		// Get an instance of the table
		$table = & JTable::getInstance('Banner','BannersTable');

		// For all keys
		foreach ($pks as $pk)
		{
			// Load the banner
			if(!$table->load($pk))
			{
				$this->setError($table->getError());
			}

			// Verify checkout
			if($table->checked_out==0 || $table->checked_out==$userId)
			{
				// Change the state
				$table->state = $state;

				// Check the row
				$table->check();

				// Store the row
				if (!$table->store())
				{
					$this->setError($table->getError());
				}
			}
		}
		return count($this->getErrors())==0;
	}
	/**
	 * Method to set the sticky state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param	mixed	An optional array of primary key values to update.  If not
	 * 					set the instance property value is used.
	 * @param	integer The sticky state. eg. [0 = unsticked, 1 = sticked]
	 * @param	integer The user id of the user performing the operation.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function stick($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k) {
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else {
				$this->setError(JText::_('No_Rows_Selected'));
				return false;
			}
		}

		// Get an instance of the table
		$table = & JTable::getInstance('Banner','BannersTable');

		// For all keys
		foreach ($pks as $pk)
		{
			// Load the banner
			if(!$table->load($pk))
			{
				$this->setError($table->getError());
			}

			// Verify checkout
			if($table->checked_out==0 || $table->checked_out==$userId)
			{
				// Change the state
				$table->sticky = $state;

				// Check the row
				$table->check();

				// Store the row
				if (!$table->store())
				{
					$this->setError($table->getError());
				}
			}
		}
		return count($this->getErrors())==0;
	}
}

