<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

class TableField extends JTable
{
	/** @var int Primary key */
	var $id = null;
	/** @var string */
	var $title = '';
	/** @var string */
	var $alias = '';
	/** @var string */
	var $description = null;
	/** @var string */
	var $type = 'text';
	/** @var int */
	var $published = 0;
	/** @var int */
	var $ordering = 0;
	/** @var int */
	var $checked_out = 0;
	/** @var time */
	var $checked_out_time	= 0;
	/** @var int */
	var $pos = 'main';
	/** @var int */
	var $access = 0;
	/** @var string */
	var $params	= null;

	/**
	* @param database A database connector object
	*/
	function __construct(&$db)
	{
		parent::__construct( '#__contactdirectory_fields', 'id', $db );
	}

	/**
	* Overloaded bind function
	*
	* @acces public
	* @param array $hash named array
	* @return null|string	null is operation was satisfactory, otherwise returns an error
	* @see JTable:bind
	* @since 1.5
	*/
	function bind($array, $ignore = '')
	{
		if (key_exists( 'params', $array ) && is_array( $array['params'] ))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check method to ensure data integrity
	 *
	 * @access public
	 * @return boolean True on success
	 * @since 1.0
	 */
	function check()
	{
		/** check for valid title */
		if (trim($this->title) == '') {
			$this->setError(JText::_('FIELD_MUST_HAVE_A_TITLE'));
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

		$xid = intval($this->_db->loadResult());
		if ($xid && $xid != intval($this->id)) {
			$this->setError(JText::sprintf('WARNNAMETRYAGAIN', JText::_('FIELD')));
			return false;
		}

		return true;
	}

	/**
	 * Overloaded store method
	 *
	 * @access public
	 * @return boolean True on success
	 * @since 1.0
	 */
	function store()
	{
		if( $this->id ) {
			if( !$this->_db->updateObject( '#__contactdirectory_fields', $this, 'id', false ) ) {
				$this->setError(get_class( $this ).'::store failed 1 - '.$this->_db->getErrorMsg());
				return false;
			}
		} else {
			$ret = $this->_db->insertObject( '#__contactdirectory_fields', $this, 'id' );
			$this->id = $this->_db->insertid();
			if( !$ret || $this->id == null) {
				$this->setError(get_class( $this ).'::store failed 2 - '.$this->_db->getErrorMsg());
				return false;
			}

			$query = "SELECT id FROM #__contactdirectory_contacts";
			$this->_db->setQuery($query);
			$contacts = $this->_db->loadObjectList();

			foreach ($contacts as $contact){
				$query = "INSERT INTO #__contactdirectory_details VALUES('$contact->id', '$this->id', '', '1', '1')";
				$this->_db->setQuery($query);
				if(!$this->_db->query()) {
					$this->setError(get_class( $this ).'::store failed 3 - '.$this->_db->getErrorMsg());
					return false;
				}
			}
		}
		return true;
	}
}